<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'price' => '29.99',
                'max_students' => 75,
                'max_staff' => 5,
                'features' => [
                    'core_sms',
                    'email_support',
                    'basic_reports',
                ],
            ],
            [
                'name' => 'Growth',
                'price' => '79.99',
                'max_students' => 250,
                'max_staff' => 15,
                'features' => [
                    'core_sms',
                    'priority_support',
                    'finance_module',
                    'attendance_insights',
                ],
            ],
            [
                'name' => 'Standard',
                'price' => '149.99',
                'max_students' => 600,
                'max_staff' => 40,
                'features' => [
                    'growth_features',
                    'custom_branding',
                    'api_access',
                    'advanced_reports',
                ],
            ],
            [
                'name' => 'Premium',
                'price' => '299.99',
                'max_students' => 2500,
                'max_staff' => 120,
                'features' => [
                    'standard_features',
                    'dedicated_manager',
                    'sla',
                    'white_label',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::query()->updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
