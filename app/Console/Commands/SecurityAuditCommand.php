<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\TwoFactorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

/**
 * `php artisan app:security-audit`
 *
 * Static, read-only review of production-relevant security knobs. Designed
 * to be run pre-deploy and as part of the security checklist documentation.
 *
 * Exit code:
 *  0 — only INFO/WARN findings
 *  1 — at least one FAIL finding
 *
 * Categories (each finding is one of):
 *   OK   — configured correctly
 *   WARN — configured non-fatally but should be reviewed
 *   FAIL — must be fixed before going live
 *
 * Findings are intentionally short strings — operators can run with
 * `--json` to integrate with deploy pipelines.
 */
class SecurityAuditCommand extends Command
{
    protected $signature = 'app:security-audit {--json : Output structured JSON}';

    protected $description = 'Audit production-relevant security configuration.';

    public function handle(): int
    {
        $findings = [];

        $env = (string) config('app.env');
        $isProd = app()->isProduction();

        $findings[] = $this->finding(
            'APP_DEBUG is OFF in production',
            ! $isProd || ! (bool) config('app.debug'),
            'FAIL'
        );

        $findings[] = $this->finding(
            'SESSION_ENCRYPT is enabled',
            (bool) config('session.encrypt', false),
            'WARN'
        );

        $findings[] = $this->finding(
            'Secure cookies in production (session.secure)',
            ! $isProd || (bool) config('session.secure'),
            'FAIL'
        );

        $findings[] = $this->finding(
            'Same-site cookies set to lax or strict',
            in_array((string) config('session.same_site', 'lax'), ['lax', 'strict'], true),
            'WARN'
        );

        $findings[] = $this->finding(
            'Password reset routing present',
            Route::has('password.request') && Route::has('password.update'),
            'FAIL'
        );

        $findings[] = $this->finding(
            'Login throttle middleware configured',
            $this->hasThrottle('login'),
            'WARN'
        );

        $findings[] = $this->finding(
            '2FA required for SuperAdmin',
            $this->superAdminsHave2fa(),
            'FAIL'
        );

        $findings[] = $this->finding(
            'Reset requires recent backup',
            (bool) config('backups.reset_requires_backup', true),
            'FAIL'
        );

        $findings[] = $this->finding(
            'Backup disk is non-public',
            ! in_array((string) config('backups.disk', 'local'), ['public', 's3-public'], true),
            'FAIL'
        );

        // Payment provider keys when enabled.
        $payIssues = [];
        foreach ((array) config('payments.providers', []) as $key => $cfg) {
            if (! ($cfg['enabled'] ?? false)) {
                continue;
            }
            if (empty($cfg['secret_key'] ?? null) && empty($cfg['api_key'] ?? null)) {
                $payIssues[] = "$key: missing API key";
            }
            if (array_key_exists('webhook_secret', $cfg) && empty($cfg['webhook_secret'])) {
                $payIssues[] = "$key: webhook secret missing";
            }
        }
        $findings[] = $this->finding(
            'Payment providers have keys + webhook secrets',
            empty($payIssues),
            'FAIL',
            implode(', ', $payIssues)
        );

        $smsIssues = [];
        foreach ((array) config('sms.providers', []) as $key => $cfg) {
            if (! ($cfg['enabled'] ?? false)) {
                continue;
            }
            if (in_array($key, ['log'], true)) {
                continue;
            }
            if (empty($cfg['api_key'] ?? null) && empty($cfg['username'] ?? null)) {
                $smsIssues[] = "$key: missing credentials";
            }
        }
        $findings[] = $this->finding(
            'SMS providers configured when enabled',
            empty($smsIssues),
            'FAIL',
            implode(', ', $smsIssues)
        );

        // No demo/default credentials.
        $defaultPasswordUsers = 0;
        foreach (User::query()->whereNotNull('password')->limit(50)->get() as $u) {
            if (Hash::check('password', (string) $u->password) || Hash::check('changeme', (string) $u->password)) {
                $defaultPasswordUsers++;
                break;
            }
        }
        $findings[] = $this->finding(
            'No users with default/demo passwords',
            $defaultPasswordUsers === 0,
            'FAIL'
        );

        // Dangerous routes protected.
        $unprotected = [];
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            $needs = [
                'platform.', 'students.destroy', 'tenants.destroy', 'reset.', 'backups.restore',
            ];
            foreach ($needs as $needle) {
                if ($name !== '' && str_starts_with($name, $needle)) {
                    $mws = $route->middleware();
                    $hasAuth = false;
                    foreach ($mws as $mw) {
                        if (str_contains((string) $mw, 'auth')) {
                            $hasAuth = true;
                            break;
                        }
                    }
                    if (! $hasAuth) {
                        $unprotected[] = $name;
                    }
                }
            }
        }
        $findings[] = $this->finding(
            'Dangerous routes are auth-protected',
            empty($unprotected),
            'FAIL',
            implode(',', array_unique($unprotected))
        );

        // Public files not exposing backups.
        $publicLeak = false;
        $publicDir = storage_path('app/public');
        if (is_dir($publicDir)) {
            foreach (['tenant_backups', 'tenant-backups', 'provisioning', 'platform/reset-snapshots'] as $needle) {
                if (is_dir($publicDir.'/'.$needle)) {
                    $publicLeak = true;
                    break;
                }
            }
        }
        $findings[] = $this->finding(
            'Public storage does not expose backups/credentials',
            ! $publicLeak,
            'FAIL'
        );

        $hasFail = collect($findings)->contains(fn ($f) => $f['level'] === 'FAIL' && ! $f['ok']);

        if ($this->option('json')) {
            $this->line(json_encode(['ok' => ! $hasFail, 'findings' => $findings], JSON_PRETTY_PRINT));
        } else {
            $this->line('Security audit — '.config('app.name'));
            $this->line(str_repeat('-', 60));
            foreach ($findings as $f) {
                $marker = $f['ok'] ? ' OK ' : $f['level'];
                $this->line(sprintf('[%s] %s%s', $marker, $f['label'], $f['detail'] ? "  ({$f['detail']})" : ''));
            }
            $this->line(str_repeat('-', 60));
            $this->line($hasFail ? 'CRITICAL FAILURES PRESENT' : 'No critical issues detected.');
        }

        return $hasFail ? 1 : 0;
    }

    private function finding(string $label, bool $ok, string $level, string $detail = ''): array
    {
        return ['label' => $label, 'ok' => $ok, 'level' => $level, 'detail' => $detail];
    }

    private function hasThrottle(string $alias): bool
    {
        try {
            $limiter = app(\Illuminate\Cache\RateLimiting\Limit::class);
        } catch (\Throwable) {
            // Falls back to checking RouteService for any throttle:login binding.
        }
        foreach (Route::getRoutes() as $r) {
            foreach ($r->middleware() as $mw) {
                if (is_string($mw) && str_contains($mw, "throttle:$alias")) {
                    return true;
                }
            }
        }

        return false;
    }

    private function superAdminsHave2fa(): bool
    {
        $svc = app(TwoFactorService::class);
        $supers = User::query()->where('role', UserRole::SuperAdmin->value)->get();
        if ($supers->isEmpty()) {
            return true;
        }
        foreach ($supers as $u) {
            if (! $svc->isEnabled($u)) {
                return false;
            }
        }

        return true;
    }
}
