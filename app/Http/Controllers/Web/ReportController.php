<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $this->authorize('reports.overview');

        return view('reports.index');
    }

    public function finance(Request $request): View
    {
        $this->authorize('reports.finance');

        [$from, $to] = $this->resolveDateRange($request);

        $paymentQuery = Payment::query()->whereBetween('date', [$from, $to]);
        $totalRevenue = (float) (clone $paymentQuery)->sum('amount');

        $expectedFees = (float) Fee::query()->sum('amount');
        $totalPaid = (float) Payment::query()->sum('amount');
        $outstandingFees = max(0.0, $expectedFees - $totalPaid);

        $feeTotalsByTerm = Fee::query()
            ->selectRaw('term_id, SUM(amount) as fee_sum')
            ->groupBy('term_id')
            ->get()
            ->keyBy('term_id');

        $terms = Term::query()->orderBy('name')->get();
        $feeGrand = (float) $feeTotalsByTerm->sum('fee_sum');
        $revenueByTerm = $terms->map(function (Term $term) use ($feeTotalsByTerm, $feeGrand, $totalRevenue): array {
            $feeSum = (float) ($feeTotalsByTerm->get($term->id)?->fee_sum ?? 0);
            $share = $feeGrand > 0 ? $feeSum / $feeGrand : ($terms->count() > 0 ? 1 / $terms->count() : 0);
            $allocated = round($totalRevenue * $share, 2);

            return [
                'term' => $term,
                'fee_sum' => $feeSum,
                'revenue_allocated' => $allocated,
            ];
        });

        $maxAllocated = max(1.0, (float) $revenueByTerm->max('revenue_allocated'));

        return view('reports.finance', [
            'from' => $from,
            'to' => $to,
            'totalRevenue' => $totalRevenue,
            'outstandingFees' => $outstandingFees,
            'revenueByTerm' => $revenueByTerm,
            'maxAllocated' => $maxAllocated,
        ]);
    }

    public function students(): View
    {
        $this->authorize('reports.students');

        $total = Student::query()->count();
        $active = Student::query()->where('status', 'active')->count();
        $inactive = $total - $active;

        $classCounts = Student::query()
            ->selectRaw('class_id, COUNT(*) as student_count')
            ->groupBy('class_id')
            ->orderByDesc('student_count')
            ->get();

        $classesById = SchoolClass::query()
            ->whereIn('id', $classCounts->pluck('class_id'))
            ->get()
            ->keyBy('id');

        $perClass = $classCounts->map(fn ($row) => [
            'class' => $classesById->get($row->class_id),
            'count' => (int) $row->student_count,
        ]);

        $genderRows = Student::query()
            ->selectRaw("COALESCE(gender, 'unspecified') as g, COUNT(*) as c")
            ->groupByRaw("COALESCE(gender, 'unspecified')")
            ->get();

        $genderMax = max(1, (int) $genderRows->max('c'));

        return view('reports.students', compact('total', 'active', 'inactive', 'perClass', 'genderRows', 'genderMax'));
    }

    public function academic(): View
    {
        $this->authorize('reports.academic');

        $avgBySubject = Result::query()
            ->selectRaw('subject_id, AVG(total) as avg_total, COUNT(*) as result_count')
            ->groupBy('subject_id')
            ->with('subject.schoolClass')
            ->get()
            ->sortByDesc('avg_total')
            ->values();

        $subjectAvgMax = max(1.0, (float) $avgBySubject->max('avg_total'));

        $topStudents = Result::query()
            ->selectRaw('student_id, AVG(total) as avg_total, COUNT(*) as subject_count')
            ->groupBy('student_id')
            ->orderByDesc('avg_total')
            ->limit(10)
            ->with('student.schoolClass')
            ->get();

        $weakSubjects = Result::query()
            ->selectRaw('subject_id, AVG(total) as avg_total, COUNT(*) as result_count')
            ->groupBy('subject_id')
            ->orderBy('avg_total')
            ->limit(5)
            ->with('subject.schoolClass')
            ->get();

        return view('reports.academic', compact('avgBySubject', 'subjectAvgMax', 'topStudents', 'weakSubjects'));
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function resolveDateRange(Request $request): array
    {
        $toInput = $request->query('date_to');
        $fromInput = $request->query('date_from');

        $to = $toInput ? Carbon::parse((string) $toInput)->endOfDay() : now()->endOfDay();
        $from = $fromInput
            ? Carbon::parse((string) $fromInput)->startOfDay()
            : (clone $to)->copy()->subMonthsNoOverflow(12)->startOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }
}
