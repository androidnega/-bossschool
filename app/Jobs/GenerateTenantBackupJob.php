<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\Tenant;
use App\Services\Backup\TenantBackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateTenantBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RunsInTenantContext, SerializesModels;

    public int $tries = 1; // never silently re-run a backup; let the operator retry.

    public function __construct(
        public int $tenantId,
        public string $type,
        public ?int $createdByUserId = null,
    ) {}

    public function handle(TenantBackupService $service): void
    {
        $this->runForTenant(function () use ($service): void {
            $tenant = Tenant::query()->findOrFail($this->tenantId);
            $user = $this->createdByUserId
                ? \App\Models\User::query()->find($this->createdByUserId)
                : null;
            $service->create($tenant, $this->type, $user);
        });
    }
}
