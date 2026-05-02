<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $tenant = Tenant::query()->create([
            'name' => 'Demo School',
            'subdomain' => 'demo',
            'plan_id' => null,
            'trial_end' => null,
            'status' => Tenant::STATUS_ACTIVE,
        ]);

        User::factory()->create([
            'tenant_id' => $tenant->id,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => UserRole::Admin->value,
        ]);
    }
}
