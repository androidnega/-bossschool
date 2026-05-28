<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Tenant;

/**
 * Centralised plan limit checks. Every "create student" or "create staff"
 * flow consults this service before saving, so any future plan field
 * (max_classes, max_users, etc.) lives in one place.
 */
class PlanLimits
{
    /**
     * @return array{plan_name:?string, max_students:?int, max_staff:?int, students_used:int, staff_used:int}
     */
    public function summary(Tenant $tenant): array
    {
        $plan = $tenant->plan ?? Plan::query()->find($tenant->plan_id);

        return [
            'plan_name' => $plan?->name,
            'max_students' => $plan?->max_students,
            'max_staff' => $plan?->max_staff,
            'students_used' => Student::query()->where('tenant_id', $tenant->id)->count(),
            'staff_used' => Staff::query()->where('tenant_id', $tenant->id)->count(),
        ];
    }

    public function canAddStudent(Tenant $tenant): bool
    {
        $summary = $this->summary($tenant);

        return $summary['max_students'] === null
            || $summary['max_students'] <= 0
            || $summary['students_used'] < $summary['max_students'];
    }

    public function canAddStaff(Tenant $tenant): bool
    {
        $summary = $this->summary($tenant);

        return $summary['max_staff'] === null
            || $summary['max_staff'] <= 0
            || $summary['staff_used'] < $summary['max_staff'];
    }

    /** Returns null if not near; otherwise a percentage 0-1.0. */
    public function studentsUsage(Tenant $tenant): ?float
    {
        $summary = $this->summary($tenant);
        if (! $summary['max_students']) {
            return null;
        }

        return $summary['students_used'] / max(1, $summary['max_students']);
    }

    public function staffUsage(Tenant $tenant): ?float
    {
        $summary = $this->summary($tenant);
        if (! $summary['max_staff']) {
            return null;
        }

        return $summary['staff_used'] / max(1, $summary['max_staff']);
    }
}
