<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Result;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\AcademicContext;
use App\Services\ReportCardData;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportCardController extends Controller
{
    public function __construct(
        private readonly ReportCardData $reportData,
        private readonly AcademicContext $academic,
    ) {}

    public function show(Request $request, Student $student): View
    {
        $this->authorize('view', $student);
        $this->authorize('viewAny', Result::class);

        $data = $this->cardPayload($student, $request);

        return view('students.report-card', $data);
    }

    public function downloadPdf(Request $request, Student $student): Response
    {
        $this->authorize('view', $student);
        $this->authorize('viewAny', Result::class);

        $data = $this->cardPayload($student, $request);

        $pdf = Pdf::loadView('students.report-card-pdf', $data)->setPaper('a4');
        $filename = Str::slug($student->name).'-report-card-'.($data['year']?->name ? str_replace('/', '-', $data['year']->name) : 'year').'-'.Str::slug($data['term']?->name ?? 'term').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Bulk: one PDF with one page per student in a given class, term and
     * academic year. Restricted to Admin, Proprietor and the teacher who
     * actually teaches the class.
     */
    public function bulkPdf(Request $request, SchoolClass $schoolClass): Response
    {
        $this->authorize('viewAny', Result::class);

        $user = $request->user();
        if ($user->role === UserRole::Teacher->value
            && ! $user->assignedClasses()->where('classes.id', $schoolClass->id)->exists()) {
            abort(403);
        }
        if (in_array($user->role, [UserRole::Accountant->value, UserRole::Parent->value, UserRole::Student->value], true)) {
            abort(403);
        }
        if ((int) $schoolClass->tenant_id !== (int) $user->tenant_id) {
            abort(403);
        }

        $year = $this->resolveYear($request);
        $term = $this->resolveTerm($request, $year);

        $students = Student::query()
            ->where('class_id', $schoolClass->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        $school = School::query()->where('tenant_id', $schoolClass->tenant_id)->first();
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $terms = Term::query()->with('academicYear')->orderBy('term_order')->get();

        $cards = $students->map(function (Student $s) use ($year, $term, $school, $years, $terms) {
            $data = $this->reportData->for($s, $year, $term);
            $data['school'] = $school;
            $data['years'] = $years;
            $data['terms'] = $terms;

            return $data;
        })->all();

        if ($cards === []) {
            abort(404, __('No active students to render.'));
        }

        $pdf = Pdf::loadView('students.report-card-pdf-bulk', [
            'cards' => $cards,
            'class' => $schoolClass,
            'term' => $term,
            'year' => $year,
        ])->setPaper('a4');

        $filename = Str::slug($schoolClass->name).'-report-cards-'.($year?->name ? str_replace('/', '-', $year->name) : '').'-'.Str::slug($term?->name ?? '').'.pdf';

        return $pdf->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function cardPayload(Student $student, Request $request): array
    {
        $year = $this->resolveYear($request);
        $term = $this->resolveTerm($request, $year);

        $data = $this->reportData->for($student, $year, $term);
        $data['school'] = School::query()->where('tenant_id', $student->tenant_id)->first();
        $data['years'] = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $data['terms'] = Term::query()->with('academicYear')->orderBy('term_order')->get();

        return $data;
    }

    private function resolveYear(Request $request): ?AcademicYear
    {
        $id = $request->integer('academic_year_id');
        if ($id) {
            $year = AcademicYear::query()->find($id);
            if ($year && (int) $year->tenant_id === (int) auth()->user()->tenant_id) {
                return $year;
            }
        }

        return $this->academic->currentYear();
    }

    private function resolveTerm(Request $request, ?AcademicYear $year): ?Term
    {
        $id = $request->integer('term_id');
        if ($id) {
            $term = Term::query()->find($id);
            if ($term && (int) $term->tenant_id === (int) auth()->user()->tenant_id) {
                return $term;
            }
        }

        return $this->academic->currentTerm();
    }
}
