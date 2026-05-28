<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * Adds light operational data to non-primary demo tenants so platform control pages are meaningful.
 */
class SecondaryTenantsDemoSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['bright-future', 'grace-valley'] as $subdomain) {
            $tenant = Tenant::query()->where('subdomain', $subdomain)->first();
            if ($tenant === null) {
                continue;
            }

            $tid = (int) $tenant->id;

            $class = SchoolClass::withoutGlobalScopes()->firstOrCreate(
                ['tenant_id' => $tid, 'name' => 'Primary 1', 'section' => null],
                []
            );

            $names = [
                ['Ato Kwesi', 'male'],
                ['Abena Koomson', 'female'],
                ['Yaw Baffoe', 'male'],
                ['Akua Dapaah', 'female'],
                ['Kojo Prah', 'male'],
            ];

            foreach ($names as $i => [$name, $gender]) {
                Student::withoutGlobalScopes()->updateOrCreate(
                    [
                        'tenant_id' => $tid,
                        'class_id' => $class->id,
                        'name' => $name,
                    ],
                    [
                        'gender' => $gender,
                        'dob' => Carbon::now()->subYears(8)->toDateString(),
                        'parent_name' => 'Demo Guardian '.($i + 1),
                        'parent_phone' => '+233240000'.str_pad((string) ($i + 200 + $tid), 3, '0', STR_PAD_LEFT),
                        'address' => 'Demo Street '.($i + 1),
                        'admission_date' => Carbon::now()->subMonths(6)->toDateString(),
                        'status' => 'active',
                    ]
                );
            }

            Staff::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tid, 'phone' => '+233200900'.str_pad((string) $tid, 2, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Ms. Platform Demo Teacher',
                    'role' => 'Teacher',
                    'subject' => 'English',
                    'salary' => '4200.00',
                ]
            );

            Staff::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tid, 'phone' => '+233200901'.str_pad((string) $tid, 2, '0', STR_PAD_LEFT)],
                [
                    'name' => 'Mr. Platform Demo Registrar',
                    'role' => 'Registrar',
                    'subject' => null,
                    'salary' => '4500.00',
                ]
            );

            $email = 'operations@'.$subdomain.'.school';

            User::withoutGlobalScopes()->updateOrCreate(
                ['tenant_id' => $tid, 'email' => $email],
                [
                    'name' => 'Operations · '.$tenant->name,
                    'password' => 'password',
                    'role' => UserRole::Admin->value,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
