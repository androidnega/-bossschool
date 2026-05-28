<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'superadmin@bossschool.com'],
            [
                'tenant_id' => null,
                'name' => 'Platform Super Admin',
                'password' => 'password',
                'role' => UserRole::SuperAdmin->value,
                'email_verified_at' => now(),
            ]
        );
    }
}
