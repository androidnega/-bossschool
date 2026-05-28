<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

/**
 * Inspect the running configuration for risky settings. Every item returns a
 * status ("ok" / "warn" / "fail") plus a short message. No secrets are read
 * or returned — only env() values that are themselves booleans/strings/keys.
 */
class ProductionChecklistService
{
    /**
     * @return array<int, array{key:string, label:string, status:string, message:string}>
     */
    public function checks(): array
    {
        $checks = [];

        $env = (string) config('app.env');
        $debug = (bool) config('app.debug');

        $checks[] = [
            'key' => 'app_env',
            'label' => 'APP_ENV',
            'status' => $env === 'production' ? 'ok' : 'warn',
            'message' => sprintf('APP_ENV is "%s". Production deployments should use "production".', $env),
        ];

        $checks[] = [
            'key' => 'app_debug',
            'label' => 'APP_DEBUG',
            'status' => $debug ? 'fail' : 'ok',
            'message' => $debug ? 'APP_DEBUG is ON — disable in production.' : 'APP_DEBUG is OFF.',
        ];

        $sessionEncrypt = (bool) config('session.encrypt', false);
        $checks[] = [
            'key' => 'session_encrypt',
            'label' => 'SESSION_ENCRYPT',
            'status' => $sessionEncrypt ? 'ok' : 'warn',
            'message' => $sessionEncrypt ? 'Sessions encrypted.' : 'SESSION_ENCRYPT=false. Enable in production for defence in depth.',
        ];

        $checks[] = [
            'key' => 'https',
            'label' => 'HTTPS / forced URL scheme',
            'status' => str_starts_with((string) config('app.url'), 'https://') ? 'ok' : 'warn',
            'message' => str_starts_with((string) config('app.url'), 'https://') ? 'APP_URL uses https.' : 'APP_URL is not https. Use HTTPS in production.',
        ];

        $sessionSecure = (bool) config('session.secure', false);
        $checks[] = [
            'key' => 'session_secure',
            'label' => 'SESSION_SECURE_COOKIE',
            'status' => $sessionSecure ? 'ok' : 'warn',
            'message' => $sessionSecure ? 'Secure cookies on.' : 'Secure cookie flag not set.',
        ];

        $checks[] = [
            'key' => 'public_storage_symlink',
            'label' => 'public/storage symlink',
            'status' => is_link(public_path('storage')) ? 'ok' : 'warn',
            'message' => is_link(public_path('storage')) ? 'public/storage symlink exists.' : 'Run `php artisan storage:link` to expose uploads.',
        ];

        $checks[] = [
            'key' => 'storage_permissions',
            'label' => 'storage/ writable',
            'status' => is_writable(storage_path()) ? 'ok' : 'fail',
            'message' => is_writable(storage_path()) ? 'storage/ is writable.' : 'storage/ is NOT writable — fix permissions.',
        ];

        $checks[] = [
            'key' => 'queue_driver',
            'label' => 'QUEUE_CONNECTION',
            'status' => in_array(config('queue.default'), ['database', 'redis', 'sqs'], true) ? 'ok' : 'warn',
            'message' => sprintf('Queue driver is "%s". Use database/redis in production with a worker running.', config('queue.default')),
        ];

        $checks[] = [
            'key' => 'demo_credentials',
            'label' => 'Demo / default passwords',
            'status' => $this->demoPasswordsPresent() ? 'fail' : 'ok',
            'message' => $this->demoPasswordsPresent()
                ? 'Demo accounts still use the default "password". Rotate before going live.'
                : 'No accounts use the default demo password.',
        ];

        $checks[] = $this->smsCheck();
        $checks[] = $this->paymentsCheck();

        return $checks;
    }

    public function summary(): array
    {
        $checks = $this->checks();
        $stats = [
            'ok' => 0,
            'warn' => 0,
            'fail' => 0,
        ];

        foreach ($checks as $c) {
            $stats[$c['status']] = ($stats[$c['status']] ?? 0) + 1;
        }

        return [
            'overall' => $stats['fail'] > 0 ? 'fail' : ($stats['warn'] > 0 ? 'warn' : 'ok'),
            'stats' => $stats,
            'checks' => $checks,
        ];
    }

    /** Scan known demo emails for users still using the literal "password". */
    public function demoPasswordsPresent(): bool
    {
        $candidates = $this->demoUserQuery()->get(['id', 'password']);

        foreach ($candidates as $u) {
            if (Hash::check('password', (string) $u->password)) {
                return true;
            }
        }

        return false;
    }

    public function demoUserQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $patterns = [
            'super@%', 'admin@%', 'proprietor@%', 'accountant@%',
            'teacher@%', 'parent@%', 'student@%',
            '%@demo.%', '%@example.%',
        ];

        $query = User::query()->withoutGlobalScopes();
        $query->where(function ($q) use ($patterns): void {
            foreach ($patterns as $p) {
                $q->orWhere('email', 'like', $p);
            }
        });

        return $query;
    }

    private function smsCheck(): array
    {
        $default = (string) config('sms.default', 'log');
        if ($default === 'log') {
            return [
                'key' => 'sms_provider',
                'label' => 'SMS provider',
                'status' => 'warn',
                'message' => 'Default SMS provider is "log" — real providers are not wired.',
            ];
        }
        $providers = (array) config('sms.providers', []);
        $cfg = $providers[$default] ?? [];
        $enabled = (bool) ($cfg['enabled'] ?? false);

        return [
            'key' => 'sms_provider',
            'label' => 'SMS provider',
            'status' => $enabled ? 'ok' : 'warn',
            'message' => $enabled ? sprintf('Default provider "%s" is enabled.', $default) : sprintf('Default provider "%s" exists but is disabled.', $default),
        ];
    }

    private function paymentsCheck(): array
    {
        $providers = (array) config('payments.providers', []);
        $enabled = collect($providers)->filter(fn ($p) => (bool) ($p['enabled'] ?? false))->keys()->all();

        return [
            'key' => 'payments_provider',
            'label' => 'Payment provider',
            'status' => count($enabled) > 0 ? 'ok' : 'warn',
            'message' => count($enabled) > 0
                ? 'Enabled providers: '.implode(', ', $enabled)
                : 'No payment provider is enabled.',
        ];
    }
}
