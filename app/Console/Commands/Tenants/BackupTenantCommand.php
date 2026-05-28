<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\Backup\TenantBackupService;
use App\Support\Tenancy;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:backup {tenant : Tenant id or subdomain} {--type=full_tenant} {--dry-run}')]
#[Description('Create a tenant backup from the CLI. Wraps the work inside Tenancy::run.')]
class BackupTenantCommand extends Command
{
    public function handle(TenantBackupService $service): int
    {
        $tenant = $this->resolveTenant((string) $this->argument('tenant'));
        if ($tenant === null) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        $type = (string) $this->option('type');
        if (! in_array($type, TenantBackup::TYPES, true)) {
            $this->error('Invalid --type. Allowed: '.implode(', ', TenantBackup::TYPES));

            return self::FAILURE;
        }

        if ($this->option('dry-run')) {
            $this->line(sprintf('Would create a "%s" backup for tenant %d (%s).', $type, $tenant->id, $tenant->subdomain));

            return self::SUCCESS;
        }

        $backup = Tenancy::run($tenant, fn () => $service->create($tenant, $type));

        if ($backup->status === TenantBackup::STATUS_COMPLETED) {
            $this->info(sprintf('Backup #%d created. size=%dB checksum=%s', $backup->id, $backup->size_bytes, substr((string) $backup->checksum, 0, 12)));

            return self::SUCCESS;
        }

        $this->error('Backup failed: '.$backup->failure_reason);

        return self::FAILURE;
    }

    private function resolveTenant(string $needle): ?Tenant
    {
        if (ctype_digit($needle)) {
            return Tenant::query()->find((int) $needle);
        }

        return Tenant::query()->where('subdomain', $needle)->first();
    }
}
