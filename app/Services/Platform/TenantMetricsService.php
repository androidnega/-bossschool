<?php

namespace App\Services\Platform;

use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TenantMetricsService
{
    /**
     * @return array<string, mixed>
     */
    public function controlCenterSummary(int $tenantId): array
    {
        $userCount = User::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        $studentCount = Student::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        $staffCount = Staff::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        $classCount = SchoolClass::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();

        $paymentsTotal = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tenantId)->sum('amount');
        $paymentsCount = Payment::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();

        $expectedFees = (float) Fee::withoutGlobalScopes()->where('tenant_id', $tenantId)->sum('amount');
        $paidTotal = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tenantId)->sum('amount');
        $outstandingEstimate = max(0.0, $expectedFees - $paidTotal);

        $resultsCount = Result::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        $attendanceCount = Attendance::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();
        $messagesCount = Message::withoutGlobalScopes()->where('tenant_id', $tenantId)->count();

        $subscription = Subscription::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('start_date')
            ->first();

        $lastActivity = $this->lastActivityAt($tenantId);

        return [
            'user_count' => $userCount,
            'student_count' => $studentCount,
            'staff_count' => $staffCount,
            'class_count' => $classCount,
            'payments_total' => $paymentsTotal,
            'payments_count' => $paymentsCount,
            'outstanding_fees_estimate' => $outstandingEstimate,
            'expected_fees_total' => $expectedFees,
            'results_count' => $resultsCount,
            'attendance_count' => $attendanceCount,
            'messages_count' => $messagesCount,
            'subscription' => $subscription,
            'last_activity_at' => $lastActivity,
        ];
    }

    private function lastActivityAt(int $tenantId): ?Carbon
    {
        $candidates = [
            Payment::withoutGlobalScopes()->where('tenant_id', $tenantId)->max('updated_at'),
            Student::withoutGlobalScopes()->where('tenant_id', $tenantId)->max('updated_at'),
            User::withoutGlobalScopes()->where('tenant_id', $tenantId)->max('updated_at'),
            Message::withoutGlobalScopes()->where('tenant_id', $tenantId)->max('updated_at'),
        ];

        $max = collect($candidates)->filter()->max();

        return $max ? Carbon::parse($max) : null;
    }

    /**
     * Debtors: students with positive balance (expected class fees − payments).
     *
     * @return array{count: int, sample_student_ids: array<int, int>}
     */
    public function debtorsSummary(int $tenantId): array
    {
        $expectedByClass = Fee::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $paidByStudent = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->selectRaw('student_id, SUM(amount) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $debtorIds = [];
        $students = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->select(['id', 'class_id'])
            ->get();

        foreach ($students as $s) {
            $expected = (float) ($expectedByClass[(int) $s->class_id] ?? 0.0);
            $paid = (float) ($paidByStudent[(int) $s->id] ?? 0.0);
            if ($expected - $paid > 0.01) {
                $debtorIds[] = (int) $s->id;
            }
        }

        return [
            'count' => count($debtorIds),
            'sample_student_ids' => array_slice($debtorIds, 0, 8),
        ];
    }

    /**
     * Average total score per subject name (tenant-wide).
     *
     * @return Collection<int, object{subject_name: string, avg_total: float, count: int}>
     */
    public function averageScoresBySubject(int $tenantId)
    {
        return Result::withoutGlobalScopes()
            ->where('results.tenant_id', $tenantId)
            ->whereNotNull('results.total')
            ->join('subjects', 'subjects.id', '=', 'results.subject_id')
            ->where('subjects.tenant_id', $tenantId)
            ->groupBy('subjects.name')
            ->orderBy('subjects.name')
            ->select([
                'subjects.name as subject_name',
                DB::raw('AVG(CAST(results.total AS DECIMAL(10,2))) as avg_total'),
                DB::raw('COUNT(*) as cnt'),
            ])
            ->get()
            ->map(fn ($row) => (object) [
                'subject_name' => $row->subject_name,
                'avg_total' => round((float) $row->avg_total, 2),
                'count' => (int) $row->cnt,
            ]);
    }

    public function studentsWithoutResultsCount(int $tenantId): int
    {
        $studentIds = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('status', 'active')
            ->pluck('id');

        if ($studentIds->isEmpty()) {
            return 0;
        }

        $withResults = Result::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('total')
            ->distinct()
            ->pluck('student_id');

        return $studentIds->diff($withResults)->count();
    }
}
