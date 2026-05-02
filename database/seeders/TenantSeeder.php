<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $starter = Plan::query()->where('name', 'Starter')->firstOrFail();

        Tenant::query()->updateOrCreate(
            ['subdomain' => 'demo'],
            [
                'name' => 'Demo School',
                'plan_id' => $starter->id,
                'trial_end' => now()->addDays(14),
                'status' => Tenant::STATUS_ACTIVE,
            ]
        );
    }
}
