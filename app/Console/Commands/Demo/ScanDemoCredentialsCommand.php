<?php

namespace App\Console\Commands\Demo;

use App\Services\ProductionChecklistService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('demo:scan-credentials')]
#[Description('Scan for accounts that still use the demo "password" credential. Never prints the password itself.')]
class ScanDemoCredentialsCommand extends Command
{
    public function handle(ProductionChecklistService $service): int
    {
        $candidates = $service->demoUserQuery()->get(['id', 'name', 'email', 'tenant_id', 'role', 'password']);
        $hits = [];

        foreach ($candidates as $u) {
            if (Hash::check('password', (string) $u->password)) {
                $hits[] = $u;
            }
        }

        if ($hits === []) {
            $this->info('No accounts use the default demo password.');

            return self::SUCCESS;
        }

        $this->warn(sprintf('%d demo accounts still use the default password:', count($hits)));
        foreach ($hits as $u) {
            $this->line(sprintf('  - #%d  %s  <%s>  tenant=%s  role=%s',
                $u->id,
                $u->name,
                $u->email,
                $u->tenant_id ?? '-',
                $u->role
            ));
        }

        return self::FAILURE;
    }
}
