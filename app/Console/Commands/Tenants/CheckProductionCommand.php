<?php

namespace App\Console\Commands\Tenants;

use App\Services\ProductionChecklistService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:check-production')]
#[Description('Run the production-readiness checklist from the CLI. Exits non-zero if any FAIL.')]
class CheckProductionCommand extends Command
{
    public function handle(ProductionChecklistService $service): int
    {
        $summary = $service->summary();
        $rows = [];
        foreach ($summary['checks'] as $c) {
            $rows[] = [$c['label'], strtoupper($c['status']), $c['message']];
        }

        $this->table(['Check', 'Status', 'Message'], $rows);
        $this->line('Overall: '.strtoupper($summary['overall']));

        return $summary['overall'] === 'fail' ? self::FAILURE : self::SUCCESS;
    }
}
