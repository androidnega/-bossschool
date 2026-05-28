<?php

namespace App\Console\Commands\Tenants;

use App\Jobs\DispatchSmsLogJob;
use App\Models\CommunicationLog;
use App\Models\Tenant;
use App\Support\Tenancy;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:send-due-sms {--tenant= : Only run for this tenant id/subdomain} {--limit=100}')]
#[Description('Dispatch every queued SMS log row, per tenant. Logs activity per tenant.')]
class SendDueSmsCommand extends Command
{
    public function handle(): int
    {
        $tenants = $this->resolveTenants();
        $limit = (int) $this->option('limit');

        foreach ($tenants as $tenant) {
            $queued = Tenancy::run($tenant, function () use ($tenant, $limit): int {
                $rows = CommunicationLog::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('channel', CommunicationLog::CHANNEL_SMS)
                    ->where('status', CommunicationLog::STATUS_QUEUED)
                    ->orderBy('id')
                    ->limit($limit)
                    ->pluck('id');

                foreach ($rows as $id) {
                    DispatchSmsLogJob::dispatch($tenant->id, (int) $id)->onQueue('sms');
                }

                return $rows->count();
            });

            $this->line(sprintf('Tenant %d (%s): queued %d SMS for dispatch.', $tenant->id, $tenant->subdomain, $queued));
        }

        return self::SUCCESS;
    }

    /** @return iterable<int, Tenant> */
    private function resolveTenants(): iterable
    {
        if ($this->option('tenant')) {
            $needle = (string) $this->option('tenant');
            $tenant = ctype_digit($needle)
                ? Tenant::query()->find((int) $needle)
                : Tenant::query()->where('subdomain', $needle)->first();

            return $tenant === null ? [] : [$tenant];
        }

        return Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->get();
    }
}
