<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Student>
 */
class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'photo' => null,
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'dob' => fake()->dateTimeBetween('-17 years', '-6 years')->format('Y-m-d'),
            'parent_name' => fake()->name(),
            'parent_phone' => fake()->numerify('##########'),
            'address' => fake()->streetAddress(),
            'admission_date' => now()->subMonths(fake()->numberBetween(1, 18))->toDateString(),
            'status' => 'active',
        ];
    }
}
