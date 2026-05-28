<?php

namespace Database\Seeders;

use App\Models\Plan;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $growth = Plan::query()->where('name', 'Growth')->firstOrFail();

        Tenant::query()->updateOrCreate(
            ['subdomain' => 'demo'],
            [
                'name' => 'Evergreen Academy (Demo School)',
                'plan_id' => $growth->id,
                'trial_end' => now()->addDays(30),
                'status' => Tenant::STATUS_ACTIVE,
            ]
        );

        Tenant::query()->updateOrCreate(
            ['subdomain' => 'bright-future'],
            [
                'name' => 'Bright Future Academy',
                'plan_id' => $growth->id,
                'trial_end' => now()->addDays(21),
                'status' => Tenant::STATUS_TRIAL,
            ]
        );

        Tenant::query()->updateOrCreate(
            ['subdomain' => 'grace-valley'],
            [
                'name' => 'Grace Valley School',
                'plan_id' => $growth->id,
                'trial_end' => null,
                'status' => Tenant::STATUS_SUSPENDED,
            ]
        );
    }
}
