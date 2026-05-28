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

class RetryFailedSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RunsInTenantContext, SerializesModels;

    public function __construct(
        public int $tenantId,
        public int $maxRows = 50,
    ) {}

    public function handle(SmsDispatcher $dispatcher): void
    {
        $this->runForTenant(function () use ($dispatcher): void {
            $rows = CommunicationLog::query()
                ->where('tenant_id', $this->tenantId)
                ->where('channel', CommunicationLog::CHANNEL_SMS)
                ->where('status', CommunicationLog::STATUS_FAILED)
                ->orderBy('id')
                ->limit($this->maxRows)
                ->get();

            foreach ($rows as $row) {
                // Reset status before retry so the dispatcher does not skip.
                $row->forceFill(['status' => CommunicationLog::STATUS_QUEUED, 'error_message' => null])->save();
                $dispatcher->dispatch($row);
            }
        });
    }
}
