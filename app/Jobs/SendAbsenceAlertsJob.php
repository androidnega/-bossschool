<?php

namespace App\Jobs;

use App\Jobs\Concerns\RunsInTenantContext;
use App\Models\SchoolClass;
use App\Services\AbsenceAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendAbsenceAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, RunsInTenantContext, SerializesModels;

    public function __construct(
        public int $tenantId,
        public int $schoolClassId,
        public string $date,
    ) {}

    public function handle(AbsenceAlertService $service): void
    {
        $this->runForTenant(function () use ($service): void {
            $class = SchoolClass::query()->find($this->schoolClassId);
            if ($class === null) {
                return;
            }
            $service->dispatchForClassDate($class, $this->date);
        });
    }
}
