<?php

namespace App\Console\Commands\Tenants;

use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\Backup\TenantBackupService;
use App\Support\Tenancy;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('tenants:export {tenant} {type=full_tenant} {--out= : Local file path to write the JSON dump to}')]
#[Description('Run an ad-hoc export (uses the backup service under the hood) for one tenant, returning the file path.')]
class ExportTenantCommand extends Command
{
    public function handle(TenantBackupService $service): int
    {
        $needle = (string) $this->argument('tenant');
        $tenant = ctype_digit($needle) ? Tenant::query()->find((int) $needle) : Tenant::query()->where('subdomain', $needle)->first();
        if ($tenant === null) {
            $this->error('Tenant not found.');

            return self::FAILURE;
        }

        $type = (string) $this->argument('type');
        if (! in_array($type, TenantBackup::TYPES, true)) {
            $this->error('Invalid type.');

            return self::FAILURE;
        }

        $payload = Tenancy::run($tenant, fn () => $service->dumpTenant($tenant->id, $type));
        $json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        $outPath = (string) ($this->option('out') ?: 'tenant-exports/'.$tenant->subdomain.'-'.$type.'-'.now()->format('Ymd-His').'.json');
        Storage::disk('local')->put($outPath, (string) $json);

        $this->info(sprintf('Exported %d row groups to local:%s', count($payload['row_counts']), $outPath));

        return self::SUCCESS;
    }
}
