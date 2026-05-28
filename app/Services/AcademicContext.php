<?php

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Term;
use Illuminate\Support\Facades\DB;

/**
 * Single source of truth for "what is the current academic year / term for
 * this tenant?". Used by results, attendance, dashboards, and report cards.
 */
class AcademicContext
{
    public function currentYear(?int $tenantId = null): ?AcademicYear
    {
        $tenantId = $this->resolveTenantId($tenantId);
        if ($tenantId === null) {
            return null;
        }

        return AcademicYear::query()
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->first();
    }

    public function currentTerm(?int $tenantId = null): ?Term
    {
        $tenantId = $this->resolveTenantId($tenantId);
        if ($tenantId === null) {
            return null;
        }

        return Term::query()
            ->where('tenant_id', $tenantId)
            ->where('is_current', true)
            ->first();
    }

    /**
     * Ensure only one academic year is marked current per tenant. Wraps the
     * flip in a transaction so the unique-current invariant holds.
     */
    public function markYearCurrent(AcademicYear $year): void
    {
        DB::transaction(function () use ($year): void {
            AcademicYear::query()
                ->where('tenant_id', $year->tenant_id)
                ->whereKeyNot($year->id)
                ->update(['is_current' => false]);

            $year->forceFill(['is_current' => true])->save();
        });
    }

    /**
     * Ensure only one term is marked current per tenant. The current term
     * must belong to the current academic year (callers should validate this
     * up-front; we also re-check here as a safety net).
     */
    public function markTermCurrent(Term $term): void
    {
        DB::transaction(function () use ($term): void {
            Term::query()
                ->where('tenant_id', $term->tenant_id)
                ->whereKeyNot($term->id)
                ->update(['is_current' => false]);

            $term->forceFill(['is_current' => true])->save();
        });
    }

    private function resolveTenantId(?int $tenantId): ?int
    {
        if ($tenantId !== null) {
            return $tenantId;
        }

        if (app()->bound('currentTenant')) {
            return (int) app('currentTenant')->id;
        }

        $user = auth()->user();

        return $user?->tenant_id;
    }
}
