<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $profiles = [
            'demo' => [
                'name' => 'Evergreen Academy',
                'address' => '14 Liberation Road, East Legon, Accra',
                'phone' => '+233302000400',
                'email' => 'office@evergreen-academy.demo',
                'motto' => 'Knowledge, Discipline, Service',
                'head_teacher_name' => 'Mr. Daniel Adjei',
                'ges_region' => 'Greater Accra',
                'ges_district' => 'La Dade Kotopon',
                'ges_circuit' => 'East Legon',
                'school_code' => 'GA-LDK-001',
            ],
            'bright-future' => [
                'name' => 'Bright Future Academy',
                'address' => 'Plot 22 Airport Residential Area, Accra',
                'phone' => '+233302000501',
                'email' => 'info@bright-future.demo',
                'motto' => 'Light the Path',
                'head_teacher_name' => 'Mrs. Akosua Mensah',
                'ges_region' => 'Greater Accra',
                'ges_district' => 'Accra Metropolitan',
                'ges_circuit' => 'Airport',
                'school_code' => 'GA-AMA-014',
            ],
            'grace-valley' => [
                'name' => 'Grace Valley School',
                'address' => 'Kumasi — Ashanti Region',
                'phone' => '+233322000600',
                'email' => 'archive@grace-valley.demo',
                'motto' => 'Grace and Wisdom',
                'head_teacher_name' => 'Mr. Kwabena Asante',
                'ges_region' => 'Ashanti',
                'ges_district' => 'Kumasi Metropolitan',
                'ges_circuit' => 'Adum',
                'school_code' => 'AS-KMA-091',
            ],
        ];

        foreach ($profiles as $subdomain => $row) {
            $tenant = Tenant::query()->where('subdomain', $subdomain)->first();
            if ($tenant === null) {
                continue;
            }

            School::query()->updateOrCreate(
                ['tenant_id' => $tenant->id],
                [
                    'name' => $row['name'],
                    'logo' => null,
                    'address' => $row['address'],
                    'phone' => $row['phone'],
                    'email' => $row['email'],
                    'academic_year' => '2025/2026',
                    'motto' => $row['motto'],
                    'head_teacher_name' => $row['head_teacher_name'],
                    'ges_region' => $row['ges_region'],
                    'ges_district' => $row['ges_district'],
                    'ges_circuit' => $row['ges_circuit'],
                    'school_code' => $row['school_code'],
                ]
            );
        }
    }
}
