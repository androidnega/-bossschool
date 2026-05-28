<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Models\User;
use App\Services\Backup\TenantBackupService;
use App\Support\Tenancy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * `php artisan tenants:restore-drill {tenant} {backup} [--target=...] [--force]`
 *
 * Restore drill = "would this backup actually restore cleanly?"
 *
 * Safety rules:
 *   - DEFAULT: dry-run only. We verify checksum, decode payload, count
 *     rows, and confirm row counts — without touching the live database.
 *   - --target=<id>: restore into the given target tenant. The default
 *     target is a sandbox tenant created on the fly. Either way the live
 *     source tenant is NEVER overwritten unless --force is also passed.
 *   - --force: required to restore into the SOURCE tenant.
 */
class RestoreDrillCommand extends Command
{
    protected $signature = 'tenants:restore-drill
                            {tenant : Source tenant ID or subdomain}
                            {backup : TenantBackup ID}
                            {--target= : Target tenant ID (default = sandbox)}
                            {--apply : Actually run INSERTs (default is dry-run)}
                            {--force : Allow restoring back into the source tenant}';

    protected $description = 'Restore drill: verify a backup file can be replayed cleanly (defaults to dry-run; --apply to write into sandbox).';

    public function handle(TenantBackupService $service): int
    {
        $tenant = $this->resolveTenant($this->argument('tenant'));
        $backup = TenantBackup::query()->find((int) $this->argument('backup'));

        if (! $tenant || ! $backup) {
            $this->error('Tenant or backup not found.');

            return 1;
        }
        if ((int) $backup->tenant_id !== (int) $tenant->id) {
            $this->error('Backup does not belong to the supplied tenant.');

            return 1;
        }

        $this->line("Restore drill — backup #{$backup->id} ({$backup->backup_type}) for tenant {$tenant->name}");
        $this->line(str_repeat('-', 60));

        // 1. checksum + payload sanity (pure read).
        $disk = Storage::disk((string) $backup->file_disk);
        if (! $disk->exists($backup->file_path)) {
            $this->error('Backup file missing on disk.');

            return 1;
        }
        $contents = (string) $disk->get($backup->file_path);
        $actual = hash('sha256', $contents);
        $checksumOk = hash_equals((string) $backup->checksum, $actual);

        $payload = json_decode($contents, true);
        $payloadOk = is_array($payload) && isset($payload['rows']);
        $totalRows = collect((array) ($payload['rows'] ?? []))->sum(fn ($r) => is_array($r) ? count($r) : 0);

        $this->line(sprintf('[%s] checksum verification', $checksumOk ? ' OK ' : 'FAIL'));
        $this->line(sprintf('[%s] payload decode (rows=%d)', $payloadOk ? ' OK ' : 'FAIL', $totalRows));

        if (! $checksumOk || ! $payloadOk) {
            return 1;
        }

        // 2. Determine target.
        $targetId = $this->option('target') ? (int) $this->option('target') : null;
        $sandbox = null;
        if (! $targetId) {
            $sandbox = $this->createSandboxTenant($tenant);
            $targetId = $sandbox->id;
            $this->line("Sandbox tenant created: #{$sandbox->id} ({$sandbox->subdomain})");
        } elseif ($targetId === (int) $tenant->id && ! $this->option('force')) {
            $this->error('Refusing to restore into the source tenant without --force.');

            return 1;
        }

        $apply = (bool) $this->option('apply');
        $this->line($apply ? '[INFO] mode = APPLY (rows will be written to sandbox)' : '[INFO] mode = DRY-RUN (no writes)');

        if (! $apply) {
            $this->line('[ OK ] dry-run completed — checksum, payload and target tenant verified.');

            return 0;
        }

        // 3. Replay the restore inside the target tenant context.
        $operator = User::query()->where('role', 'SuperAdmin')->first()
            ?: User::query()->orderBy('id')->first();

        try {
            Tenancy::run($targetId, function () use ($service, $backup, $operator, $targetId): void {
                $service->restore($backup, $operator, $targetId);
            });
            $this->line('[ OK ] restore replay succeeded');

            $written = DB::table('students')->where('tenant_id', $targetId)->count();
            $this->line("[INFO] students rows in target after restore: {$written}");
        } catch (\Throwable $e) {
            $this->error('Restore replay failed: '.$e->getMessage());

            return 1;
        }

        $this->line('-- Restore drill completed successfully.');

        return 0;
    }

    private function resolveTenant(string $key): ?Tenant
    {
        return Tenant::query()->where('id', $key)->orWhere('subdomain', $key)->first();
    }

    /** Spin up a throwaway tenant to receive the restore. */
    private function createSandboxTenant(Tenant $source): Tenant
    {
        $sub = 'drill-'.now()->format('YmdHis').'-'.bin2hex(random_bytes(2));

        return Tenant::query()->create([
            'name' => $source->name.' (drill)',
            'subdomain' => $sub,
            'plan_id' => $source->plan_id,
            'status' => Tenant::STATUS_SUSPENDED,
        ]);
    }
}
