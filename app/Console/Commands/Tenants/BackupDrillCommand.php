<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\Backup\TenantBackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * `php artisan tenants:backup-drill {tenant} [--type=full] [--keep]`
 *
 * Operational rehearsal of the backup pipeline:
 *
 *   1. Resolve the tenant.
 *   2. Run TenantBackupService::create() end-to-end.
 *   3. Re-read the file from disk and recompute its SHA-256 checksum.
 *   4. Decode the manifest and confirm:
 *        - every row in every table has tenant_id == drill tenant id
 *        - the row counts in the manifest match the dump
 *   5. Print a drill report and (unless --keep) remove the drill backup.
 *
 * Designed to be scheduled weekly as a "do we actually have working
 * backups?" check.
 */
class BackupDrillCommand extends Command
{
    protected $signature = 'tenants:backup-drill
                            {tenant : ID or subdomain}
                            {--type=full_tenant : Backup type}
                            {--keep : Keep the drill backup row + file}';

    protected $description = 'Run a backup drill against a tenant and verify checksum/manifest.';

    public function handle(TenantBackupService $service): int
    {
        $tenant = $this->resolveTenant($this->argument('tenant'));
        if (! $tenant) {
            $this->error('Tenant not found.');

            return 1;
        }

        $this->line("Backup drill — tenant: {$tenant->name} ({$tenant->subdomain})");
        $this->line(str_repeat('-', 60));

        $backup = $service->create($tenant, (string) $this->option('type'));

        $report = ['steps' => []];

        $report['steps']['create'] = [
            'ok' => $backup->status === TenantBackup::STATUS_COMPLETED,
            'detail' => "id={$backup->id} status={$backup->status}",
        ];

        if (! $report['steps']['create']['ok']) {
            $report['steps']['create']['detail'] .= ' / reason='.$backup->failure_reason;
            $this->renderReport($report);

            return 1;
        }

        // Re-read + checksum verify.
        $disk = Storage::disk((string) $backup->file_disk);
        $contents = (string) $disk->get($backup->file_path);
        $actual = hash('sha256', $contents);
        $checksumOk = hash_equals((string) $backup->checksum, $actual);
        $report['steps']['checksum'] = ['ok' => $checksumOk, 'detail' => $checksumOk ? 'sha256 match' : 'sha256 MISMATCH'];

        // Decode + manifest sanity.
        $payload = json_decode($contents, true);
        $rows = (array) ($payload['rows'] ?? []);
        $manifestRows = (array) ($payload['row_counts'] ?? []);

        $mismatch = [];
        foreach ($manifestRows as $table => $claimed) {
            $actualCount = is_array($rows[$table] ?? null) ? count($rows[$table]) : 0;
            if ((int) $claimed !== $actualCount) {
                $mismatch[$table] = "manifest=$claimed actual=$actualCount";
            }
        }
        $report['steps']['manifest'] = [
            'ok' => empty($mismatch),
            'detail' => empty($mismatch) ? 'all row counts match' : json_encode($mismatch),
        ];

        // Tenant-only data?
        $foreignTenantRows = 0;
        foreach ($rows as $table => $tableRows) {
            foreach ($tableRows as $row) {
                if (isset($row['tenant_id']) && (int) $row['tenant_id'] !== (int) $tenant->id) {
                    $foreignTenantRows++;
                }
            }
        }
        $report['steps']['tenant_isolation'] = [
            'ok' => $foreignTenantRows === 0,
            'detail' => $foreignTenantRows === 0 ? 'no foreign-tenant rows' : "$foreignTenantRows foreign rows present",
        ];

        $report['steps']['size'] = ['ok' => $backup->size_bytes > 0, 'detail' => $backup->size_bytes.' bytes'];

        $allOk = collect($report['steps'])->every(fn ($s) => $s['ok']);

        if (! $this->option('keep')) {
            $disk->delete($backup->file_path);
            $backup->delete();
            $report['cleanup'] = 'drill backup removed';
        } else {
            $report['cleanup'] = 'drill backup kept on disk';
        }

        $this->renderReport($report);

        return $allOk ? 0 : 1;
    }

    private function resolveTenant(string $key): ?Tenant
    {
        return Tenant::query()->where('id', $key)
            ->orWhere('subdomain', $key)
            ->first();
    }

    private function renderReport(array $report): void
    {
        foreach ((array) ($report['steps'] ?? []) as $name => $step) {
            $this->line(sprintf('[%s] %s — %s', $step['ok'] ? ' OK ' : 'FAIL', $name, $step['detail']));
        }
        if (isset($report['cleanup'])) {
            $this->line('-- '.$report['cleanup']);
        }
    }
}
