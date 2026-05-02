<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Term;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();

        $classOne = SchoolClass::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Grade 1',
                'section' => 'A',
            ]
        );

        $classTwo = SchoolClass::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Grade 2',
                'section' => 'A',
            ]
        );

        Term::query()->firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'name' => 'Term 1',
            ]
        );

        Staff::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'phone' => '+233241100001',
            ],
            [
                'name' => 'Mary Johnson',
                'role' => 'Head Teacher',
                'subject' => 'Mathematics',
                'salary' => '4500.00',
            ]
        );

        Staff::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'phone' => '+233241100002',
            ],
            [
                'name' => 'Kwame Asante',
                'role' => 'Teacher',
                'subject' => 'English',
                'salary' => '3800.00',
            ]
        );

        $students = [
            ['class' => $classOne, 'name' => 'Ama Serwaa', 'gender' => 'female'],
            ['class' => $classOne, 'name' => 'Kofi Mensah', 'gender' => 'male'],
            ['class' => $classOne, 'name' => 'Efua Boateng', 'gender' => 'female'],
            ['class' => $classTwo, 'name' => 'Yaw Owusu', 'gender' => 'male'],
            ['class' => $classTwo, 'name' => 'Abena Frimpong', 'gender' => 'female'],
        ];

        foreach ($students as $row) {
            Student::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'class_id' => $row['class']->id,
                    'name' => $row['name'],
                ],
                [
                    'gender' => $row['gender'],
                    'dob' => now()->subYears(10)->subMonths(fake()->numberBetween(0, 11))->toDateString(),
                    'parent_name' => 'Parent of '.$row['name'],
                    'parent_phone' => '+23324'.fake()->numerify('#######'),
                    'address' => 'Demo Estate, Block '.fake()->numberBetween(1, 9),
                    'admission_date' => now()->subMonths(fake()->numberBetween(3, 14))->toDateString(),
                    'status' => 'active',
                ]
            );
        }
    }
}
