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

        $demoUsers = [
            [
                'email' => 'admin@demo.com',
                'name' => 'Demo Admin',
                'role' => UserRole::Admin,
            ],
            [
                'email' => 'proprietor@demo.com',
                'name' => 'Demo Proprietor',
                'role' => UserRole::Proprietor,
            ],
            [
                'email' => 'accountant@demo.com',
                'name' => 'Demo Accountant',
                'role' => UserRole::Accountant,
            ],
            [
                'email' => 'teacher@demo.com',
                'name' => 'Demo Teacher',
                'role' => UserRole::Teacher,
            ],
        ];

        foreach ($demoUsers as $row) {
            User::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'email' => $row['email'],
                ],
                [
                    'name' => $row['name'],
                    'password' => 'password',
                    'role' => $row['role']->value,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
