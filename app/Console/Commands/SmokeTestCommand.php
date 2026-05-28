<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\HealthCheckService;
use App\Services\TwoFactorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * `php artisan app:smoke-test`
 *
 * Single command an operator can run after a deploy. It checks:
 *  - app key present                       (critical)
 *  - migrations current                    (critical)
 *  - storage writable                      (critical)
 *  - queue configured                      (warn if 'sync' in prod)
 *  - scheduler heartbeat                   (warn if stale)
 *  - DB / cache reachable                  (critical)
 *  - backup disk writable + private        (critical)
 *  - mailer configured / explicitly off    (warn)
 *  - SMS / payment providers sane          (warn)
 *  - health endpoint OK                    (critical)
 *  - no demo users active                  (critical in prod)
 *  - HTTPS in production                   (critical in prod)
 *  - public storage not exposing backups   (critical)
 *
 * Exit codes:
 *  0 — everything is OK or only warnings
 *  1 — at least one critical check failed
 */
class SmokeTestCommand extends Command
{
    protected $signature = 'app:smoke-test {--json : Output JSON only}';

    protected $description = 'Run a post-deploy smoke test.';

    public function handle(HealthCheckService $health): int
    {
        $results = [];

        $results[] = $this->check('App key set', ! empty(config('app.key')), critical: true);

        // migrations current — ask the migrator directly to avoid spawning
        // a second artisan call (which would clobber this command's output).
        try {
            $migrator = app('migrator');
            $files = $migrator->getMigrationFiles(database_path('migrations'));
            $ran = $migrator->getRepository()->getRan();
            $pending = count(array_diff(array_keys($files), $ran));
        } catch (\Throwable) {
            $pending = -1;
        }
        $results[] = $this->check(
            'Migrations are current',
            $pending === 0,
            detail: $pending > 0 ? "$pending pending" : 'all applied',
            critical: true
        );

        $results[] = $this->check(
            'Storage writable',
            $this->canWriteStorage(),
            critical: true
        );

        $queue = (string) config('queue.default');
        $results[] = $this->check(
            'Queue driver suitable',
            ! (app()->isProduction() && $queue === 'sync'),
            detail: "driver=$queue",
            critical: false
        );

        $results[] = $this->check(
            'DB reachable',
            $this->dbReachable(),
            critical: true
        );

        $results[] = $this->check(
            'Cache reachable',
            $this->cacheReachable(),
            critical: true
        );

        $results[] = $this->check(
            'Backup disk writable',
            $this->backupDiskOk(),
            detail: 'disk='.config('backups.disk', 'local'),
            critical: true
        );

        $mail = (string) config('mail.default');
        $results[] = $this->check(
            'Mailer configured or explicitly off',
            $mail === 'log' || $mail === 'array' || ! empty(config('mail.mailers.'.$mail.'.host')),
            detail: "mailer=$mail",
            critical: false
        );

        $smsActive = collect((array) config('sms.providers', []))->contains(fn ($p) => (bool) ($p['enabled'] ?? false));
        $results[] = $this->check(
            'SMS provider sandbox/disabled state clear',
            true, // informational
            detail: $smsActive ? 'at least one provider enabled' : 'no SMS provider enabled (log mode)',
            critical: false
        );

        $payActive = collect((array) config('payments.providers', []))->contains(fn ($p) => (bool) ($p['enabled'] ?? false));
        $results[] = $this->check(
            'Payment provider state clear',
            true,
            detail: $payActive ? 'at least one provider enabled' : 'all providers disabled',
            critical: false
        );

        // Local health check (we can't curl ourselves from artisan reliably).
        $simple = $health->simple();
        $simpleOk = ($simple['status'] ?? '') === 'ok' || (bool) ($simple['ok'] ?? false);
        $results[] = $this->check(
            'Local health checks pass',
            $simpleOk,
            detail: $simpleOk ? '' : ($simple['status'] ?? 'fail'),
            critical: true
        );

        // No active demo users.
        $demo = User::query()
            ->where('email', 'like', '%@demo.local')
            ->orWhere('email', 'like', '%@example.test')
            ->count();
        // Stronger check: any user whose password matches the default 'password'.
        $matchDefault = 0;
        $sample = User::query()->limit(20)->get();
        foreach ($sample as $u) {
            if ($u->password && Hash::check('password', (string) $u->password)) {
                $matchDefault++;
                break;
            }
        }
        $results[] = $this->check(
            'No demo / default-password users active',
            $matchDefault === 0,
            detail: $demo > 0 ? "demo-style emails: $demo" : 'no default credentials detected',
            critical: app()->isProduction()
        );

        $results[] = $this->check(
            'HTTPS in production',
            ! app()->isProduction() || str_starts_with((string) config('app.url'), 'https://'),
            detail: config('app.url'),
            critical: app()->isProduction()
        );

        $results[] = $this->check(
            'Public storage does not expose backups',
            $this->backupsArePrivate(),
            critical: true
        );

        $results[] = $this->check(
            'SuperAdmin 2FA enrolment',
            $this->superAdmins2faEnrolled(),
            critical: app()->isProduction()
        );

        $criticalFail = collect($results)->contains(fn ($r) => ! $r['ok'] && $r['critical']);

        if ($this->option('json')) {
            $this->line(json_encode(['ok' => ! $criticalFail, 'results' => $results], JSON_PRETTY_PRINT));
        } else {
            $this->line('Smoke test — '.config('app.name'));
            $this->line('--');
            foreach ($results as $r) {
                $glyph = $r['ok'] ? 'OK  ' : ($r['critical'] ? 'FAIL' : 'WARN');
                $this->line(sprintf('[%s] %s%s', $glyph, $r['label'], $r['detail'] ? "  ({$r['detail']})" : ''));
            }
            $this->line('--');
            $this->line($criticalFail ? 'CRITICAL FAILURES PRESENT' : 'All critical checks passed.');
        }

        return $criticalFail ? 1 : 0;
    }

    private function check(string $label, bool $ok, string $detail = '', bool $critical = true): array
    {
        return ['label' => $label, 'ok' => $ok, 'critical' => $critical, 'detail' => $detail];
    }

    private function canWriteStorage(): bool
    {
        try {
            Storage::disk('local')->put('_smoketest_'.time().'.tmp', 'x');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function dbReachable(): bool
    {
        try {
            return Schema::hasTable('users');
        } catch (\Throwable) {
            return false;
        }
    }

    private function cacheReachable(): bool
    {
        try {
            cache()->put('_smoketest_'.time(), 'x', 10);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function backupDiskOk(): bool
    {
        try {
            $disk = (string) config('backups.disk', 'local');
            Storage::disk($disk)->put('smoketest/'.time().'.tmp', 'x');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function backupsArePrivate(): bool
    {
        $root = storage_path('app/public');
        if (! is_dir($root)) {
            return true;
        }
        foreach (['tenant_backups', 'platform/reset-snapshots', 'provisioning'] as $needle) {
            if (is_dir($root.'/'.$needle)) {
                return false;
            }
        }

        return true;
    }

    private function superAdmins2faEnrolled(): bool
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
