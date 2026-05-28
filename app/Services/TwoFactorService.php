<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Self-contained TOTP (RFC 6238) + recovery codes for the platform.
 *
 * Why hand-rolled instead of pragmarx/google2fa:
 *  - The algorithm is small (HOTP/TOTP is ~30 lines of PHP).
 *  - We control every byte that lands in storage/logs.
 *  - One fewer external dependency to vet for the pilot.
 *
 * Storage:
 *  - two_factor_secret             : Crypt::encryptString(raw base32 secret)
 *  - two_factor_recovery_codes     : Crypt::encryptString(JSON-encoded array of Hash::make() hashes)
 *  - two_factor_confirmed_at       : timestamp when the user successfully verified the first code
 *
 * Audit / log policy:
 *  - The secret is never logged or returned in plain text outside of the
 *    one-time setup screen.
 *  - The recovery codes are returned ONCE at generation; only their hashed
 *    form is persisted.
 *  - PermissionService / audit logger are never given the raw secret.
 */
class TwoFactorService
{
    public const ROLES_REQUIRING_2FA = [
        UserRole::SuperAdmin->value,
    ];

    /** Roles a tenant may force 2FA on via the require_2fa_for_admins setting. */
    public const ROLES_TENANT_MAY_REQUIRE = [
        UserRole::Proprietor->value,
        UserRole::Admin->value,
        UserRole::Headteacher->value,
        UserRole::Accountant->value,
    ];

    /** Generate a 20-byte (160 bit) base32 secret suitable for TOTP. */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * Generate $count fresh recovery codes. The plaintext codes are returned
     * so the UI can show them ONCE. Only the hashes are stored.
     *
     * @return array{plain: array<int, string>, hashed: array<int, string>}
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $plain = [];
        $hashed = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(5).'-'.Str::random(5));
            $plain[] = $code;
            $hashed[] = Hash::make($code);
        }

        return ['plain' => $plain, 'hashed' => $hashed];
    }

    /**
     * Start the enrolment flow for $user. Generates and persists a fresh
     * encrypted secret + recovery code hashes. Returns the plaintext
     * artefacts the UI needs to render: the secret, the otpauth URI, and
     * the unhashed recovery codes (shown only once).
     *
     * @return array{secret: string, otpauth_uri: string, recovery_codes: array<int, string>}
     */
    public function beginSetup(User $user): array
    {
        $secret = $this->generateSecret();
        $codes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_secret' => Crypt::encryptString($secret),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode($codes['hashed'])),
            'two_factor_confirmed_at' => null,
        ])->save();

        return [
            'secret' => $secret,
            'otpauth_uri' => $this->otpauthUri(
                (string) config('app.name', 'School Manager'),
                (string) $user->email,
                $secret
            ),
            'recovery_codes' => $codes['plain'],
        ];
    }

    /**
     * Verify a 6-digit TOTP code from the user's authenticator app. Accepts
     * a small ±1 step window for clock skew (≈30s on either side).
     */
    public function verifyTotp(User $user, string $code): bool
    {
        if (! $user->two_factor_secret) {
            return false;
        }
        $secret = Crypt::decryptString((string) $user->two_factor_secret);
        $code = preg_replace('/\D/', '', $code);
        if (strlen((string) $code) !== 6) {
            return false;
        }

        $timestep = (int) floor(time() / 30);
        for ($i = -1; $i <= 1; $i++) {
            if (hash_equals($this->totpAt($secret, $timestep + $i), (string) $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verify a recovery code. On success the code is consumed (removed from
     * the user's stored set) and the function returns true.
     */
    public function consumeRecoveryCode(User $user, string $code): bool
    {
        if (! $user->two_factor_recovery_codes) {
            return false;
        }
        $hashes = (array) json_decode(Crypt::decryptString((string) $user->two_factor_recovery_codes), true);
        $code = strtoupper(trim($code));

        foreach ($hashes as $i => $hash) {
            if (Hash::check($code, (string) $hash)) {
                unset($hashes[$i]);
                $user->forceFill([
                    'two_factor_recovery_codes' => Crypt::encryptString(json_encode(array_values($hashes))),
                ])->save();

                return true;
            }
        }

        return false;
    }

    /**
     * Finalise enrolment after the user successfully verifies their first
     * TOTP code on the confirmation screen.
     */
    public function confirm(User $user): void
    {
        $user->forceFill(['two_factor_confirmed_at' => now()])->save();
    }

    public function disable(User $user): void
    {
        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    public function isEnabled(User $user): bool
    {
        return ! empty($user->two_factor_secret) && ! empty($user->two_factor_confirmed_at);
    }

    public function requiresEnrolment(User $user): bool
    {
        if ($this->isEnabled($user)) {
            return false;
        }

        if (in_array($user->role, self::ROLES_REQUIRING_2FA, true)) {
            return true;
        }

        if (in_array($user->role, self::ROLES_TENANT_MAY_REQUIRE, true) && $user->tenant_id) {
            $required = (bool) app(TenantSettings::class)->get((int) $user->tenant_id, 'require_2fa_for_admins', false);

            return $required;
        }

        return false;
    }

    /**
     * Build a standard otpauth URI; authenticator apps render it as a QR.
     * Issuer + label both contain the app name and the user's email.
     */
    public function otpauthUri(string $issuer, string $accountName, string $secret): string
    {
        $label = rawurlencode($issuer.':'.$accountName);
        $params = http_build_query([
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => 6,
            'period' => 30,
        ]);

        return 'otpauth://totp/'.$label.'?'.$params;
    }

    /**
     * Render an otpauth URI as an inline SVG QR code, generated server-side
     * via bacon/bacon-qr-code. No external service is contacted, so the
     * secret in the URI never leaves this process before being delivered as
     * part of the authenticated HTML response.
     */
    public function qrSvg(string $otpauthUri, int $size = 220): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd(),
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($otpauthUri);

        // Strip the XML prolog so the SVG can be embedded inline in HTML
        // without breaking the surrounding document.
        return (string) preg_replace('/^<\?xml.*?\?>\s*/i', '', $svg);
    }

    /** Compute the 6-digit TOTP code for a base32 secret at a given timestep. */
    public function totpAt(string $secret, int $timestep): string
    {
        $binarySecret = $this->base32Decode($secret);
        $packed = pack('J', $timestep); // 64-bit big-endian
        $hash = hash_hmac('sha1', $packed, $binarySecret, true);
        $offset = ord($hash[strlen($hash) - 1]) & 0x0F;
        $code = (((ord($hash[$offset]) & 0x7F) << 24)
            | ((ord($hash[$offset + 1]) & 0xFF) << 16)
            | ((ord($hash[$offset + 2]) & 0xFF) << 8)
            | (ord($hash[$offset + 3]) & 0xFF));

        return str_pad((string) ($code % 1000000), 6, '0', STR_PAD_LEFT);
    }

    private function base32Encode(string $bytes): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $bits = '';
        for ($i = 0, $n = strlen($bytes); $i < $n; $i++) {
            $bits .= str_pad(decbin(ord($bytes[$i])), 8, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 5) as $chunk) {
            if (strlen($chunk) < 5) {
                $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
            }
            $out .= $alphabet[bindec($chunk)];
        }

        return $out;
    }

    private function base32Decode(string $secret): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = strtoupper(preg_replace('/[^A-Z2-7]/', '', $secret));
        $bits = '';
        for ($i = 0, $n = strlen($secret); $i < $n; $i++) {
            $val = strpos($alphabet, $secret[$i]);
            if ($val === false) {
                continue;
            }
            $bits .= str_pad(decbin($val), 5, '0', STR_PAD_LEFT);
        }
        $out = '';
        foreach (str_split($bits, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $out .= chr(bindec($chunk));
            }
        }

        return $out;
    }
}
