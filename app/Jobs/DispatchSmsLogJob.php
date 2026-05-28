<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\CommunicationLog;
use App\Services\Sms\SmsDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DispatchSmsLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RunsInTenantContext, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public int $tenantId,
        public int $communicationLogId,
    ) {}

    public function handle(SmsDispatcher $dispatcher): void
    {
        $this->runForTenant(function () use ($dispatcher): void {
            $log = CommunicationLog::query()->find($this->communicationLogId);
            if ($log === null) {
                return;
            }
            $dispatcher->dispatch($log);
        });
    }
}
