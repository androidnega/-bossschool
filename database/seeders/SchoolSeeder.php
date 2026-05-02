<?php

namespace Database\Seeders;

use App\Models\School;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->where('subdomain', 'demo')->firstOrFail();

        School::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => 'Demo School',
                'logo' => null,
                'address' => '12 Education Avenue, Accra',
                'phone' => '+233241000000',
                'email' => 'office@demo-school.local',
                'academic_year' => now()->year.'/'.(now()->year + 1),
            ]
        );
    }
}
