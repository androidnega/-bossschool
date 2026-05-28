<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Fee;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * Produce a per-tenant onboarding checklist. The checklist is purely a UI
 * helper — it never blocks anything by itself. Each step returns true/false
 * plus a link/route the Admin can click to complete that step.
 */
class OnboardingChecklistService
{
    public function __construct(private readonly TenantSettings $tenantSettings) {}

    /**
     * @return array{percent_complete:int, items:array<int, array{key:string,label:string,done:bool,route:?string,detail:?string}>}
     */
    public function forTenant(Tenant $tenant): array
    {
        $school = $tenant->school()->first();

        $items = [
            [
                'key' => 'school_profile',
                'label' => 'School profile completed',
                'done' => (bool) $school && trim((string) $school->name) !== '',
                'route' => 'settings.index',
                'detail' => $school?->name ? "Profile: {$school->name}" : null,
            ],
            [
                'key' => 'academic_year',
                'label' => 'Academic year created',
                'done' => AcademicYear::query()->where('tenant_id', $tenant->id)->exists(),
                'route' => 'academic-years.index',
                'detail' => null,
            ],
            [
                'key' => 'terms',
                'label' => 'Terms created',
                'done' => Term::query()->where('tenant_id', $tenant->id)->count() >= 3,
                'route' => 'terms.index',
                'detail' => null,
            ],
            [
                'key' => 'classes',
                'label' => 'Classes created',
                'done' => SchoolClass::query()->where('tenant_id', $tenant->id)->exists(),
                'route' => 'classes.index',
                'detail' => null,
            ],
            [
                'key' => 'staff',
                'label' => 'Staff imported',
                'done' => Staff::query()->where('tenant_id', $tenant->id)->exists(),
                'route' => 'staff.index',
                'detail' => null,
            ],
            [
                'key' => 'students',
                'label' => 'Students imported',
                'done' => Student::query()->where('tenant_id', $tenant->id)->exists(),
                'route' => 'students.index',
                'detail' => null,
            ],
            [
                'key' => 'fees',
                'label' => 'Fees configured',
                'done' => Fee::query()->where('tenant_id', $tenant->id)->exists(),
                'route' => 'fees.index',
                'detail' => null,
            ],
            [
                'key' => 'sms_provider',
                'label' => 'SMS provider configured',
                'done' => (string) $this->tenantSettings->get($tenant->id, 'default_sms_provider', '') !== '',
                'route' => 'settings.tenant',
                'detail' => null,
            ],
            [
                'key' => 'online_payment',
                'label' => 'Online payment provider configured',
                'done' => collect((array) config('payments.providers', []))
                    ->contains(fn ($p) => (bool) ($p['enabled'] ?? false)),
                'route' => 'settings.tenant',
                'detail' => null,
            ],
            [
                'key' => 'first_backup',
                'label' => 'First backup completed',
                'done' => TenantBackup::query()
                    ->where('tenant_id', $tenant->id)
                    ->where('status', TenantBackup::STATUS_COMPLETED)
                    ->exists(),
                'route' => 'backups.index',
                'detail' => null,
            ],
        ];

        $done = collect($items)->where('done', true)->count();
        $percent = (int) round(($done / max(1, count($items))) * 100);

        return [
            'percent_complete' => $percent,
            'items' => $items,
        ];
    }
}
