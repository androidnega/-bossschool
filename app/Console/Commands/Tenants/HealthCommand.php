<?php

namespace App\Console\Commands\Tenants;

use App\Services\HealthCheckService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('tenants:health')]
#[Description('Run platform-wide health checks (db, cache, queue, storage, mail, sms, payments, backups).')]
class HealthCommand extends Command
{
    public function handle(HealthCheckService $service): int
    {
        $result = $service->detailed();
        $rows = [];
        foreach ($result['checks'] as $name => $check) {
            $rows[] = [$name, ($check['ok'] ?? false) ? 'OK' : 'FAIL', (string) ($check['message'] ?? '')];
        }
        $this->table(['Check', 'Status', 'Message'], $rows);
        $this->line('Overall: '.strtoupper($result['status']));

        return $result['status'] === 'ok' ? self::SUCCESS : self::FAILURE;
    }
}
