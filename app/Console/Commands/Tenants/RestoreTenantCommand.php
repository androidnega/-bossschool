<?php

namespace App\Console\Commands\Tenants;

use App\Enums\UserRole;
use App\Models\TenantBackup;
use App\Models\User;
use App\Services\Backup\TenantBackupService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:restore {backup_id : tenant_backups.id} {--target=} {--dry-run} {--force}')]
#[Description('Restore a backup. Verifies sha256 checksum first; refuses on mismatch. Requires --force to actually write.')]
class RestoreTenantCommand extends Command
{
    public function handle(TenantBackupService $service): int
    {
        $backup = TenantBackup::query()->find((int) $this->argument('backup_id'));
        if ($backup === null) {
            $this->error('Backup not found.');

            return self::FAILURE;
        }

        if (! $service->verifyChecksum($backup)) {
            $this->error('Checksum verification FAILED. Refusing to restore.');

            return self::FAILURE;
        }

        $this->info(sprintf('Backup #%d for tenant %d, type %s — checksum OK.', $backup->id, $backup->tenant_id, $backup->backup_type));

        if ($this->option('dry-run')) {
            $this->line('Dry run: no rows written.');

            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            $this->warn('Restore is destructive (additive). Pass --force to proceed.');

            return self::FAILURE;
        }

        $target = $this->option('target') ? (int) $this->option('target') : null;

        // Use any SuperAdmin as the restorer for the audit log.
        $restorer = User::query()->where('role', UserRole::SuperAdmin->value)->first();
        if ($restorer === null) {
            $this->error('No SuperAdmin user found to record the restore against.');

            return self::FAILURE;
        }

        try {
            $service->restore($backup, $restorer, $target);
            $this->info('Restore completed.');

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Restore failed: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
