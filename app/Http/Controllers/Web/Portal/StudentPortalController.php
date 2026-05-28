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
use App\Models\Term;
use App\Services\AcademicContext;
use App\Services\ReportCardData;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentPortalController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $student = $user->student;

        if ($student === null) {
            abort(404);
        }

        $student->load(['schoolClass', 'results.subject']);
        $tenantId = (int) $user->tenant_id;

        $expected = (float) Fee::query()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $student->class_id)
            ->sum('amount');

        $paid = (float) Payment::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->sum('amount');

        $feeBalance = max(0.0, $expected - $paid);

        $attendance = Attendance::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->orderByDesc('date')
            ->limit(14)
            ->get();

        $messages = Message::query()
            ->visibleToStudent($student)
            ->with(['sender', 'schoolClass'])
            ->limit(12)
            ->get();

        $latestResults = Result::query()
            ->where('tenant_id', $tenantId)
            ->where('student_id', $student->id)
            ->with('subject')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        $subjects = $latestResults->pluck('subject')->filter()->unique('id')->values();

        return view('portal.student.index', compact(
            'student',
            'feeBalance',
            'attendance',
            'messages',
            'latestResults',
            'subjects'
        ));
    }

    public function reportCard(Request $request, ReportCardData $reportData, AcademicContext $academic): View
    {
        $student = $this->resolveOwnStudent($request);
        $data = $this->buildReportData($student, $reportData, $academic);

        return view('students.report-card', $data);
    }

    public function reportCardPdf(Request $request, ReportCardData $reportData, AcademicContext $academic): \Symfony\Component\HttpFoundation\Response
    {
        $student = $this->resolveOwnStudent($request);
        $data = $this->buildReportData($student, $reportData, $academic);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('students.report-card-pdf', $data)->setPaper('a4');

        return $pdf->download(\Illuminate\Support\Str::slug($student->name).'-report-card.pdf');
    }

    private function resolveOwnStudent(Request $request): \App\Models\Student
    {
        $user = $request->user();
        $student = $user->student;

        if ($student === null) {
            abort(404);
        }

        $this->authorize('view', $student);

        return $student;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(\App\Models\Student $student, ReportCardData $reportData, AcademicContext $academic): array
    {
        $year = $academic->currentYear();
        $term = $academic->currentTerm();
        $data = $reportData->for($student, $year, $term);
        $data['school'] = School::query()->where('tenant_id', $student->tenant_id)->first();
        $data['years'] = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $data['terms'] = Term::query()->with('academicYear')->orderBy('term_order')->get();

        return $data;
    }
}
