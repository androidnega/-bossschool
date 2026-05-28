<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\TwoFactorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Self-service 2FA setup/disable for a logged-in user.
 *
 * Routes:
 *   GET  /two-factor              show status + setup CTA
 *   POST /two-factor/enable       generate secret + recovery codes, stash plaintext
 *                                 codes in session, redirect to GET /two-factor/enable
 *   GET  /two-factor/enable       render the QR + recovery codes from session
 *                                 (survives refresh + validation errors on confirm)
 *   POST /two-factor/confirm      verify first 6-digit code, mark confirmed
 *   POST /two-factor/disable      require current password; clears all 2FA columns
 *
 * The enable flow uses Post-Redirect-Get so that:
 *   - refreshing the QR page does not 405
 *   - an invalid code on /two-factor/confirm round-trips back to the QR page
 *     with the validation error and the same recovery codes still visible
 */
class TwoFactorController extends Controller
{
    /** Session key for the one-time plaintext recovery codes during setup. */
    private const SETUP_SESSION_KEY = 'two_factor.setup';

    public function __construct(
        private readonly TwoFactorService $service,
        private readonly ActivityLogger $logger,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();

        return view('two_factor.show', [
            'enabled' => $this->service->isEnabled($user),
            'required' => $this->service->requiresEnrolment($user),
        ]);
    }

    /**
     * Hitting POST-only `/two-factor/confirm` or `/two-factor/disable` with a
     * GET (browser refresh after a POST result, or the user typed the URL)
     * used to 405. Redirect back to the 2FA hub instead.
     *
     * Note: GET `/two-factor/enable` is intentionally NOT routed here — it
     * renders the QR + recovery codes from session via `enableSetup()`.
     */
    public function redirectToShow(): RedirectResponse
    {
        return redirect()
            ->route('two-factor.show')
            ->with('status', __('Please use the Enable / Confirm / Disable buttons on this page to manage two-factor authentication.'));
    }

    /**
     * Begin enrolment: generate a fresh secret + recovery codes, stash the
     * plaintext recovery codes in session (they are never persisted in
     * plaintext, only here for the next render), then redirect to the setup
     * view. This is the POST handler.
     */
    public function enable(Request $request): RedirectResponse
    {
        $user = $request->user();
        $setup = $this->service->beginSetup($user);

        $this->logger->log('2fa_setup_started', '2FA setup initiated', ['user_id' => $user->id], $user->tenant_id, $user::class, $user->id);

        $request->session()->put(self::SETUP_SESSION_KEY, [
            'recovery_codes' => $setup['recovery_codes'],
        ]);

        return redirect()->route('two-factor.enable.show');
    }

    /**
     * Render the QR + recovery codes from session. This is the GET twin of
     * `enable()`. If no active setup is in session (or the user is already
     * enrolled) we send them back to the 2FA hub.
     */
    public function enableSetup(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $setup = $request->session()->get(self::SETUP_SESSION_KEY);

        if ($this->service->isEnabled($user)
            || ! is_array($setup)
            || empty($setup['recovery_codes'])
            || empty($user->two_factor_secret)
        ) {
            return redirect()->route('two-factor.show');
        }

        $secret = Crypt::decryptString((string) $user->two_factor_secret);
        $otpauthUri = $this->service->otpauthUri(
            (string) config('app.name', 'School Manager'),
            (string) $user->email,
            $secret
        );

        return view('two_factor.enable', [
            'secret' => $secret,
            'otpauth_uri' => $otpauthUri,
            'qr_svg' => $this->service->qrSvg($otpauthUri),
            'recovery_codes' => $setup['recovery_codes'],
        ]);
    }

    public function confirm(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);
        $user = $request->user();

        if (! $this->service->verifyTotp($user, $data['code'])) {
            throw ValidationException::withMessages(['code' => __('Invalid 2FA code.')]);
        }

        $this->service->confirm($user);
        $this->logger->log('2fa_enabled', '2FA enabled', ['user_id' => $user->id], $user->tenant_id, $user::class, $user->id);
        $request->session()->put('two_factor_passed', true);
        $request->session()->forget(self::SETUP_SESSION_KEY);

        // Forward the user to their actual landing page after enrolment.
        // Falling through to /two-factor here would loop them back to the
        // same hub they just came from, which feels like nothing happened.
        $destination = $request->session()->pull('url.intended') ?? $user->homeRoute();

        return redirect()->to($destination)
            ->with('status', __('Two-factor authentication enabled.'));
    }

    public function disable(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'password' => ['required', 'string'],
        ]);
        $user = $request->user();

        if (! Hash::check((string) $data['password'], (string) $user->password)) {
            throw ValidationException::withMessages(['password' => __('Password incorrect.')]);
        }

        if ($this->service->requiresEnrolment($user) || in_array($user->role, TwoFactorService::ROLES_REQUIRING_2FA, true)) {
            throw ValidationException::withMessages(['password' => __('2FA is required for your role and cannot be disabled.')]);
        }

        $this->service->disable($user);
        $this->logger->log('2fa_disabled', '2FA disabled', ['user_id' => $user->id], $user->tenant_id, $user::class, $user->id);

        return redirect()->route('two-factor.show')->with('status', __('Two-factor authentication disabled.'));
    }
}
