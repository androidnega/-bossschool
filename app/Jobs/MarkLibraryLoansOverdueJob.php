<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\LibraryLoan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MarkLibraryLoansOverdueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RunsInTenantContext, SerializesModels;

    public function __construct(public int $tenantId) {}

    public function handle(): void
    {
        $this->runForTenant(function (): void {
            LibraryLoan::query()
                ->where('tenant_id', $this->tenantId)
                ->where('status', LibraryLoan::STATUS_BORROWED)
                ->whereNotNull('due_at')
                ->whereDate('due_at', '<', now()->toDateString())
                ->update(['status' => LibraryLoan::STATUS_OVERDUE]);
        });
    }
}
