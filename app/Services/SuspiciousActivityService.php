<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Carbon;

/**
 * Surface "interesting" audit log patterns to operators.
 *
 * Each detector returns a small bag of counters + the rows used. None of
 * these patterns alone are a guarantee of misuse — they are flags that
 * deserve human review.
 */
class SuspiciousActivityService
{
    public const LOGIN_FAIL_THRESHOLD = 5;

    public const PAYMENT_REVERSAL_THRESHOLD = 3;

    public const CROSS_TENANT_DENIED_THRESHOLD = 1;

    /**
     * Run all detectors for the given tenant (or all tenants for SuperAdmin).
     *
     * @return array<int, array{kind:string, severity:string, count:int, message:string, rows: array<int, array<string, mixed>>}>
     */
    public function findFor(?int $tenantId, Carbon $since): array
    {
        $out = [];

        $out[] = $this->manyFailedLogins($tenantId, $since);
        $out[] = $this->manyPaymentReversals($tenantId, $since);
        $out[] = $this->resultEditsAfterTermClose($tenantId, $since);
        $out[] = $this->crossTenantDenied($tenantId, $since);
        $out[] = $this->resetAttempts($tenantId, $since);

        return array_values(array_filter($out, fn ($x) => ($x['count'] ?? 0) > 0));
    }

    private function manyFailedLogins(?int $tenantId, Carbon $since): array
    {
        $q = ActivityLog::query()
            ->where('action', 'login_failed')
            ->where('created_at', '>=', $since);

        if ($tenantId !== null) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->orderByDesc('created_at')->limit(50)->get()->map->toArray()->all();
        $count = count($rows);

        return [
            'kind' => 'many_failed_logins',
            'severity' => $count >= self::LOGIN_FAIL_THRESHOLD ? 'high' : 'low',
            'count' => $count,
            'message' => sprintf('%d failed logins since %s', $count, $since->format('Y-m-d H:i')),
            'rows' => $rows,
        ];
    }

    private function manyPaymentReversals(?int $tenantId, Carbon $since): array
    {
        $q = ActivityLog::query()
            ->where('action', 'payment_reversed')
            ->where('created_at', '>=', $since);

        if ($tenantId !== null) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->orderByDesc('created_at')->limit(50)->get()->map->toArray()->all();
        $count = count($rows);

        return [
            'kind' => 'many_payment_reversals',
            'severity' => $count >= self::PAYMENT_REVERSAL_THRESHOLD ? 'high' : 'low',
            'count' => $count,
            'message' => sprintf('%d payment reversals since %s', $count, $since->format('Y-m-d H:i')),
            'rows' => $rows,
        ];
    }

    private function resultEditsAfterTermClose(?int $tenantId, Carbon $since): array
    {
        $q = ActivityLog::query()
            ->where('action', 'result_updated_after_close')
            ->where('created_at', '>=', $since);

        if ($tenantId !== null) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->orderByDesc('created_at')->limit(50)->get()->map->toArray()->all();
        $count = count($rows);

        return [
            'kind' => 'result_edits_after_close',
            'severity' => $count > 0 ? 'high' : 'low',
            'count' => $count,
            'message' => sprintf('%d result edits after term close', $count),
            'rows' => $rows,
        ];
    }

    private function crossTenantDenied(?int $tenantId, Carbon $since): array
    {
        $q = ActivityLog::query()
            ->whereIn('action', ['cross_tenant_denied', 'tenant_isolation_violation'])
            ->where('created_at', '>=', $since);

        if ($tenantId !== null) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->orderByDesc('created_at')->limit(50)->get()->map->toArray()->all();
        $count = count($rows);

        return [
            'kind' => 'cross_tenant_denied',
            'severity' => $count >= self::CROSS_TENANT_DENIED_THRESHOLD ? 'high' : 'low',
            'count' => $count,
            'message' => sprintf('%d cross-tenant denied access events', $count),
            'rows' => $rows,
        ];
    }

    private function resetAttempts(?int $tenantId, Carbon $since): array
    {
        $q = ActivityLog::query()
            ->whereIn('action', ['tenant_data_reset', 'system_data_reset'])
            ->where('created_at', '>=', $since);

        if ($tenantId !== null) {
            $q->where('tenant_id', $tenantId);
        }
        $rows = $q->orderByDesc('created_at')->limit(50)->get()->map->toArray()->all();
        $count = count($rows);

        return [
            'kind' => 'reset_attempts',
            'severity' => $count > 0 ? 'high' : 'low',
            'count' => $count,
            'message' => sprintf('%d destructive reset events', $count),
            'rows' => $rows,
        ];
    }
}
