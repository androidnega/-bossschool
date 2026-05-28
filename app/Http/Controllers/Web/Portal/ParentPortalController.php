<?php

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Message;
use App\Models\Payment;
use App\Models\Result;
use App\Models\School;
use App\Models\Student;
use App\Models\Term;
use App\Services\AcademicContext;
use App\Services\ReportCardData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ParentPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $children = $user->children()->with('schoolClass')->orderBy('name')->get();
        $tenantId = (int) $user->tenant_id;

        $balances = $children->mapWithKeys(function (Student $child) use ($tenantId): array {
            return [$child->id => $this->outstandingForStudent($tenantId, $child)];
        });

        $childIds = $children->pluck('id');
        $recentPayments = Payment::query()
            ->where('tenant_id', $tenantId)
            ->whereIn('student_id', $childIds)
            ->with('student')
            ->orderByDesc('date')
            ->limit(6)
            ->get();

        $messages = Message::query()
            ->visibleToParent($user)
            ->with(['sender', 'schoolClass'])
            ->limit(12)
            ->get();

        $since = Carbon::today()->subDays(14)->toDateString();

        $attendanceSummaries = $children->mapWithKeys(function (Student $child) use ($tenantId, $since): array {
            $rows = Attendance::query()
                ->where('tenant_id', $tenantId)
                ->where('student_id', $child->id)
                ->where('date', '>=', $since)
                ->get();

            return [$child->id => [
                'present' => $rows->where('status', 'present')->count(),
                'total' => $rows->count(),
            ]];
        });

        $latestResults = $children->mapWithKeys(function (Student $child) use ($tenantId): array {
            return [$child->id => Result::query()
                ->where('tenant_id', $tenantId)
                ->where('student_id', $child->id)
                ->with('subject')
                ->orderByDesc('updated_at')
                ->limit(3)
                ->get()];
        });

        return view('portal.parent.index', compact(
            'children',
            'balances',
            'recentPayments',
            'messages',
            'attendanceSummaries',
            'latestResults'
        ));
    }

    public function child(Request $request, Student $student): View
    {
        $this->authorize('view', $student);

        $student->load('schoolClass');
        $tenantId = (int) $request->user()->tenant_id;
        $balance = $this->outstandingForStudent($tenantId, $student);

        $payments = Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(15)
            ->get();

        $results = Result::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->with('subject')
            ->orderBy('subject_id')
            ->get();

        $attendance = Attendance::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(14)
            ->get();

        return view('portal.parent.child', compact('student', 'balance', 'payments', 'results', 'attendance'));
    }

    public function reportCard(Request $request, Student $student, ReportCardData $reportData, AcademicContext $academic): View
    {
        $this->authorize('view', $student);

        $data = $this->buildReportData($student, $reportData, $academic);

        return view('students.report-card', $data);
    }

    public function reportCardPdf(Request $request, Student $student, ReportCardData $reportData, AcademicContext $academic): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('view', $student);

        if (! $request->user()->isGuardianOf($student)) {
            abort(403);
        }

        $data = $this->buildReportData($student, $reportData, $academic);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.report-card-pdf', $data)->setPaper('a4');

        return $pdf->download(\Illuminate\Support\Str::slug($student->name).'-report-card.pdf');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Student $student, ReportCardData $reportData, AcademicContext $academic): array
    {
        $year = $academic->currentYear();
        $term = $academic->currentTerm();
        $data = $reportData->for($student, $year, $term);
        $data['school'] = School::query()->where('tenant_id', $student->tenant_id)->first();
        $data['years'] = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $data['terms'] = Term::query()->with('academicYear')->orderBy('term_order')->get();

        return $data;
    }

    private function outstandingForStudent(int $tenantId, Student $student): float
    {
        $expected = (float) Fee::query()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $student->class_id)
            ->sum('amount');

        $paid = (float) Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->sum('amount');

        return max(0.0, $expected - $paid);
    }
}
