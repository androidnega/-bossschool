<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();

        User::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'email' => 'admin@demo.com',
            ],
            [
                'name' => 'Super Admin',
                'password' => 'password',
                'role' => UserRole::Admin->value,
                'email_verified_at' => now(),
            ]
        );
    }
}
