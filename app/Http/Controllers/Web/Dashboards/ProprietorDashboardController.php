<?php

namespace App\Http\Controllers\Web\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\View\View;

class ProprietorDashboardController extends Controller
{
    public function __invoke(): View
    {
        $studentStats = Student::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active")
            ->first();

        $studentCount = (int) ($studentStats->total ?? 0);
        $activeStudentCount = (int) ($studentStats->active ?? 0);

        $feesCollected = (float) Payment::query()->sum('amount');

        $expectedByClass = Fee::query()
            ->selectRaw('class_id, SUM(amount) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $paidByStudent = Payment::query()
            ->selectRaw('student_id, SUM(amount) as total')
            ->groupBy('student_id')
            ->pluck('total', 'student_id');

        $outstandingFees = (float) Student::query()
            ->get(['id', 'class_id'])
            ->sum(function (Student $s) use ($expectedByClass, $paidByStudent): float {
                $expected = (float) ($expectedByClass[(int) $s->class_id] ?? 0);
                $paid = (float) ($paidByStudent[(int) $s->id] ?? 0);

                return max(0.0, $expected - $paid);
            });

        $debtorsCount = Student::query()
            ->get(['id', 'class_id'])
            ->filter(function (Student $s) use ($expectedByClass, $paidByStudent): bool {
                $expected = (float) ($expectedByClass[(int) $s->class_id] ?? 0);
                $paid = (float) ($paidByStudent[(int) $s->id] ?? 0);

                return $expected - $paid > 0.01;
            })
            ->count();

        $paymentsToday = Payment::query()->whereDate('date', Carbon::today())->count();

        $staffCount = Staff::query()->count();

        $attendanceToday = Attendance::query()->whereDate('date', Carbon::today())->count();

        $tenant = app('currentTenant');
        $subscription = Subscription::query()
            ->where('tenant_id', $tenant->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->orderByDesc('end_date')
            ->first();

        $growth = Student::query()
            ->where('created_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn (Student $s) => $s->created_at?->format('Y-m') ?? '')
            ->map(fn ($group) => $group->count())
            ->sortKeys();

        $recentPayments = Payment::query()
            ->with(['student.schoolClass'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $topDebtors = Student::query()
            ->with('schoolClass')
            ->orderBy('name')
            ->get()
            ->map(function (Student $s) use ($expectedByClass, $paidByStudent): array {
                $expected = (float) ($expectedByClass[(int) $s->class_id] ?? 0);
                $paid = (float) ($paidByStudent[(int) $s->id] ?? 0);

                return [
                    'student' => $s,
                    'balance' => max(0.0, $expected - $paid),
                ];
            })
            ->sortByDesc('balance')
            ->values()
            ->filter(fn (array $row): bool => $row['balance'] > 0.01)
            ->take(5);

        $recentAdmissions = Student::query()
            ->with('schoolClass')
            ->orderByDesc('admission_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        $recentNotices = Message::query()
            ->with(['sender', 'schoolClass'])
            ->recentForProprietorDashboard()
            ->limit(8)
            ->get();

        return view('dashboards.proprietor', compact(
            'studentCount',
            'activeStudentCount',
            'feesCollected',
            'outstandingFees',
            'debtorsCount',
            'paymentsToday',
            'staffCount',
            'attendanceToday',
            'subscription',
            'growth',
            'recentPayments',
            'topDebtors',
            'recentAdmissions',
            'recentNotices'
        ));
    }
}
