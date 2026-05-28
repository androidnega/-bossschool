<?php

namespace App\Http\Controllers\Web\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\View\View;

class AccountantDashboardController extends Controller
{
    public function __invoke(): View
    {
        $paymentsToday = Payment::query()->whereDate('date', Carbon::today());
        $paymentsTodayCount = (clone $paymentsToday)->count();
        $paymentsTodaySum = (float) (clone $paymentsToday)->sum('amount');

        $totalCollections = (float) Payment::query()->sum('amount');

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

        $recentPayments = Payment::query()
            ->with(['student.schoolClass'])
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $methods = Payment::query()
            ->selectRaw('payment_channel as method, COUNT(*) as c, SUM(amount) as total')
            ->where('status', Payment::STATUS_SUCCESSFUL)
            ->groupBy('payment_channel')
            ->get();

        $highestDebtors = Student::query()
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

        return view('dashboards.accountant', compact(
            'paymentsTodayCount',
            'paymentsTodaySum',
            'totalCollections',
            'outstandingFees',
            'debtorsCount',
            'recentPayments',
            'methods',
            'highestDebtors'
        ));
    }
}
