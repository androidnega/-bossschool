<?php

namespace App\Console\Commands\Demo;

use App\Services\ActivityLogger;
use App\Services\ProductionChecklistService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

#[Signature('demo:disable-users {--dry-run : List affected accounts without changing anything} {--force : Skip confirmation prompt}')]
#[Description('Soft-disable demo accounts that still use the default password by setting a strong random password and clearing their remember_token.')]
class DisableDemoUsersCommand extends Command
{
    public function handle(ProductionChecklistService $service, ActivityLogger $logger): int
    {
        $candidates = $service->demoUserQuery()->get();
        $targets = $candidates->filter(fn ($u) => Hash::check('password', (string) $u->password));

        if ($targets->isEmpty()) {
            $this->info('No demo accounts to disable.');

            return self::SUCCESS;
        }

        $dryRun = (bool) $this->option('dry-run');

        $this->line(sprintf('%s %d demo accounts:', $dryRun ? 'Would disable' : 'Disabling', $targets->count()));
        foreach ($targets as $u) {
            $this->line('  - #'.$u->id.'  '.$u->email);
        }

        if ($dryRun) {
            return self::SUCCESS;
        }

        if (! $this->option('force') && $this->input->isInteractive()) {
            if (! $this->confirm('Proceed?', false)) {
                return self::SUCCESS;
            }
        }

        foreach ($targets as $u) {
            $u->forceFill([
                'password' => bcrypt(Str::random(48)),
                'remember_token' => null,
            ])->save();

            $logger->log(
                'demo_user_disabled',
                sprintf('Demo account %s disabled (random password set).', $u->email),
                ['user_id' => $u->id, 'email' => $u->email],
                $u->tenant_id ? (int) $u->tenant_id : null,
                \App\Models\User::class,
                (int) $u->id
            );
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
