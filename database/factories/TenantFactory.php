<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'subdomain' => fake()->unique()->regexify('[a-z]{3,12}'),
            'plan_id' => null,
            'trial_end' => null,
            'status' => Tenant::STATUS_ACTIVE,
        ];
    }
}
