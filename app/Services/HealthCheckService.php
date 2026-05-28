<?php

namespace App\Services;

use App\Models\CommunicationLog;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantBackup;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Run a battery of checks against the infrastructure the app depends on.
 *
 * Each check returns ['ok' => bool, 'message' => string, ...details].
 * The aggregated `status` is `ok` only when every individual check is ok.
 *
 * No secrets are returned — only counts, booleans and short messages.
 */
class HealthCheckService
{
    /**
     * @return array{status:string, checks:array<string, array<string,mixed>>}
     */
    public function detailed(): array
    {
        $checks = [
            'database' => $this->database(),
            'cache' => $this->cache(),
            'queue' => $this->queue(),
            'storage' => $this->storage(),
            'mail' => $this->mailConfig(),
            'sms' => $this->smsConfig(),
            'payments' => $this->paymentsConfig(),
            'failed_jobs' => $this->failedJobs(),
            'failed_sms' => $this->failedSms(),
            'tenants' => $this->tenants(),
            'subscriptions' => $this->activeSubscriptions(),
            'backups_disk' => $this->backupsDisk(),
        ];

        $allOk = ! collect($checks)->contains(fn ($c) => ! ($c['ok'] ?? false));

        return [
            'status' => $allOk ? 'ok' : 'degraded',
            'checks' => $checks,
        ];
    }

    /**
     * The minimal /health output. We don't expose check details here on
     * purpose — public surface returns OK/FAIL only.
     */
    public function simple(): array
    {
        try {
            DB::connection()->getPdo();
        } catch (Throwable $e) {
            return ['status' => 'fail'];
        }

        return ['status' => 'ok'];
    }

    private function database(): array
    {
        try {
            DB::connection()->getPdo();

            return ['ok' => true, 'driver' => DB::connection()->getDriverName(), 'message' => 'Database reachable'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Database unreachable: '.$e->getMessage()];
        }
    }

    private function cache(): array
    {
        try {
            $key = 'health.'.bin2hex(random_bytes(4));
            Cache::put($key, 'pong', 5);
            $val = Cache::get($key);
            Cache::forget($key);

            return ['ok' => $val === 'pong', 'driver' => (string) config('cache.default'), 'message' => $val === 'pong' ? 'Cache read/write ok' : 'Cache round-trip failed'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Cache failed: '.$e->getMessage()];
        }
    }

    private function queue(): array
    {
        $driver = (string) config('queue.default', 'sync');

        if ($driver === 'sync') {
            return ['ok' => true, 'driver' => $driver, 'message' => 'sync (no worker required, but production should use database/redis)'];
        }

        if ($driver === 'database') {
            try {
                $count = (int) DB::table('jobs')->count();

                return ['ok' => true, 'driver' => $driver, 'pending' => $count, 'message' => 'database queue reachable'];
            } catch (Throwable $e) {
                return ['ok' => false, 'driver' => $driver, 'message' => 'Queue table unreachable'];
            }
        }

        return ['ok' => true, 'driver' => $driver, 'message' => 'Queue configured'];
    }

    private function storage(): array
    {
        $disk = (string) config('filesystems.default', 'local');
        try {
            $key = 'health/'.bin2hex(random_bytes(6)).'.txt';
            Storage::disk($disk)->put($key, 'pong');
            $val = Storage::disk($disk)->get($key);
            Storage::disk($disk)->delete($key);

            return ['ok' => $val === 'pong', 'disk' => $disk, 'message' => 'Storage writable'];
        } catch (Throwable $e) {
            return ['ok' => false, 'disk' => $disk, 'message' => 'Storage not writable'];
        }
    }

    private function mailConfig(): array
    {
        $driver = (string) config('mail.default', 'log');
        if ($driver === 'log') {
            return ['ok' => true, 'driver' => $driver, 'message' => 'Mail uses log driver (acceptable in non-production only)'];
        }
        $host = (string) config('mail.mailers.smtp.host', '');

        return ['ok' => $host !== '', 'driver' => $driver, 'message' => $host !== '' ? 'SMTP host configured' : 'SMTP host missing'];
    }

    private function smsConfig(): array
    {
        $default = (string) config('sms.default', 'log');
        $providers = (array) config('sms.providers', []);
        if ($default === 'log') {
            return ['ok' => true, 'provider' => $default, 'message' => 'log provider only (test mode)'];
        }
        $cfg = $providers[$default] ?? [];
        $enabled = (bool) ($cfg['enabled'] ?? false);

        return ['ok' => $enabled, 'provider' => $default, 'message' => $enabled ? 'Provider enabled' : 'Default SMS provider is disabled or unset'];
    }

    private function paymentsConfig(): array
    {
        $providers = (array) config('payments.providers', []);
        $enabled = collect($providers)->filter(fn ($p) => (bool) ($p['enabled'] ?? false))->keys()->all();

        return [
            'ok' => count($enabled) > 0,
            'enabled' => $enabled,
            'message' => count($enabled) > 0 ? 'At least one payment provider enabled' : 'No payment provider enabled',
        ];
    }

    private function failedJobs(): array
    {
        try {
            $count = (int) DB::table('failed_jobs')->count();

            return ['ok' => $count === 0, 'count' => $count, 'message' => $count === 0 ? 'No failed jobs' : sprintf('%d failed jobs in queue', $count)];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'failed_jobs table missing'];
        }
    }

    private function failedSms(): array
    {
        try {
            $count = (int) CommunicationLog::query()
                ->where('channel', CommunicationLog::CHANNEL_SMS)
                ->where('status', CommunicationLog::STATUS_FAILED)
                ->count();

            return ['ok' => $count < 10, 'count' => $count, 'message' => sprintf('%d failed SMS rows', $count)];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'communication_logs unreachable'];
        }
    }

    private function tenants(): array
    {
        return [
            'ok' => true,
            'count' => (int) Tenant::query()->count(),
            'active' => (int) Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->count(),
            'message' => 'Tenant counts',
        ];
    }

    private function activeSubscriptions(): array
    {
        try {
            $count = (int) Subscription::query()->where('status', 'active')->count();

            return ['ok' => true, 'count' => $count, 'message' => sprintf('%d active subscriptions', $count)];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Subscription read failed'];
        }
    }

    private function backupsDisk(): array
    {
        $disk = (string) config('backups.disk', 'local');
        try {
            $last = TenantBackup::query()->orderByDesc('id')->first();
            $age = $last?->created_at?->diffForHumans() ?? 'never';

            return [
                'ok' => true,
                'disk' => $disk,
                'last_backup_at' => $age,
                'message' => $last ? 'Most recent backup '.$age : 'No backups have been taken yet',
            ];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Backup catalogue unreachable'];
        }
    }
}
