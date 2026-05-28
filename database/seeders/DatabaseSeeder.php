<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            PlansSeeder::class,
            TenantSeeder::class,
            SchoolSeeder::class,
            SuperAdminSeeder::class,
            PlatformBootstrapSeeder::class,
            PermissionsSeeder::class,
            // Production-safe Ghana school setup templates (Primary / JHS /
            // Primary+JHS / Full Basic). Idempotent — safe to re-run.
            GhanaBasicSchoolTemplateSeeder::class,
            AdminUserSeeder::class,
            DemoDataSeeder::class,
            RolePortalSeeder::class,
            DemoMessagesSeeder::class,
            SecondaryTenantsDemoSeeder::class,
        ]);
    }
}
