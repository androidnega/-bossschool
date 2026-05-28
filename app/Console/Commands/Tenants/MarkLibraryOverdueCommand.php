<?php

namespace App\Console\Commands\Tenants;

use App\Jobs\MarkLibraryLoansOverdueJob;
use App\Models\Tenant;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:mark-library-overdue')]
#[Description('Dispatch the overdue-marking job for every active tenant.')]
class MarkLibraryOverdueCommand extends Command
{
    public function handle(): int
    {
        $count = 0;
        foreach (Tenant::query()->where('status', Tenant::STATUS_ACTIVE)->get() as $tenant) {
            MarkLibraryLoansOverdueJob::dispatch($tenant->id);
            $count++;
        }
        $this->info(sprintf('Dispatched overdue-marking job for %d tenants.', $count));

        return self::SUCCESS;
    }
}
