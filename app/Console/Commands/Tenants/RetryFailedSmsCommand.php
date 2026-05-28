<?php

namespace App\Console\Commands\Tenants;

use App\Jobs\RetryFailedSmsJob;
use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:retry-failed-sms {--tenant=}')]
#[Description('Re-attempt every failed SMS row for one tenant or all active tenants.')]
class RetryFailedSmsCommand extends Command
{
    public function handle(): int
    {
        $tenants = $this->option('tenant')
            ? Tenant::query()->where(function ($q): void {
                $q->where('id', (int) $this->option('tenant'))->orWhere('subdomain', (string) $this->option('tenant'));
            })->get()
            : Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->get();

        foreach ($tenants as $tenant) {
            RetryFailedSmsJob::dispatch($tenant->id)->onQueue('sms');
            $this->line('Dispatched retry job for tenant '.$tenant->id);
        }

        return self::SUCCESS;
    }
}
