<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\RolePortalSeeder;
use Database\Seeders\SuperAdminSeeder;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('demo:reset-credentials {--force : Run without confirmation prompt}')]
#[Description('Reset demo account passwords to "password" and re-sync roles + portal pivots (SuperAdmin, school staff, parent, student, teacher links)')]
class ResetDemoCredentialsCommand extends Command
{
    public function handle(): int
    {
        if (! Tenant::query()->where('subdomain', 'demo')->exists()) {
            $this->error('No tenant with subdomain "demo". Run: php artisan db:seed (or migrate:fresh --seed) first.');

            return self::FAILURE;
        }

        if (! $this->option('force') && $this->input->isInteractive()) {
            if (! $this->confirm('Reset all demo passwords to "password" and re-sync RolePortal pivots?', true)) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $this->info('Running SuperAdminSeeder…');
        $this->call(SuperAdminSeeder::class);

        $this->info('Running AdminUserSeeder…');
        $this->call(AdminUserSeeder::class);

        $this->info('Running RolePortalSeeder…');
        $this->call(RolePortalSeeder::class);

        $this->newLine();
        $this->info('Done. Log in with password `password` (see PRIVILEGES_AND_PASSWORDS.txt for emails).');

        return self::SUCCESS;
    }
}
