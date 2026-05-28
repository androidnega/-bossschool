<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportCardMeta\UpdateReportCardMetaRequest;
use App\Models\AcademicYear;
use App\Models\ReportCardMeta;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Policies\ReportCardMetaPolicy;
use App\Services\AcademicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Per-(student, year, term) report-card metadata: attendance summary,
 * conduct / attitude / interest, class & head teacher remarks, next-term fee,
 * vacation / reopening dates, signatures.
 *
 * Access is role-aware: Admin / Proprietor see and write everything; Teachers
 * see students in their assigned classes and can only write a restricted set
 * (see {@see ReportCardMetaPolicy::editableFieldsFor()}).
 */
class ReportCardMetaController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ReportCardMeta::class);

        $user = $request->user();
        $classes = $this->classesVisibleTo($user);
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $terms = Term::query()->with('academicYear')->orderBy('term_order')->get();

        $classId = $request->integer('class_id') ?: $classes->first()?->id;
        $yearId = $request->integer('academic_year_id') ?: $this->academic->currentYear()?->id;
        $termId = $request->integer('term_id') ?: $this->academic->currentTerm()?->id;

        $students = $classId
            ? Student::query()
                ->where('class_id', $classId)
                ->whereIn('status', ['active', 'inactive'])
                ->orderBy('name')
                ->get()
            : collect();

        $existing = collect();
        if ($classId && $yearId && $termId && $students->isNotEmpty()) {
            $existing = ReportCardMeta::query()
                ->whereIn('student_id', $students->pluck('id'))
                ->where('academic_year_id', $yearId)
                ->where('term_id', $termId)
                ->get()
                ->keyBy('student_id');
        }

        return view('report-card-meta.index', [
            'classes' => $classes,
            'years' => $years,
            'terms' => $terms,
            'students' => $students,
            'existing' => $existing,
            'classId' => $classId,
            'yearId' => $yearId,
            'termId' => $termId,
        ]);
    }

    public function edit(Request $request, Student $student): View
    {
        $policy = app(ReportCardMetaPolicy::class);
        if (! $policy->editForStudent($request->user(), $student)) {
            abort(403);
        }

        [$year, $term] = $this->resolvePeriod($request);

        $meta = $year && $term
            ? ReportCardMeta::query()
                ->where('tenant_id', $student->tenant_id)
                ->where('student_id', $student->id)
                ->where('academic_year_id', $year->id)
                ->where('term_id', $term->id)
                ->first()
            : null;

        return view('report-card-meta.edit', [
            'student' => $student->load('schoolClass'),
            'year' => $year,
            'term' => $term,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get(),
            'terms' => Term::query()->with('academicYear')->orderBy('term_order')->get(),
            'meta' => $meta,
            'editableFields' => ReportCardMetaPolicy::editableFieldsFor($request->user()),
        ]);
    }

    public function update(UpdateReportCardMetaRequest $request, Student $student): RedirectResponse
    {
        [$year, $term] = $this->resolvePeriod($request);

        if ($year === null || $term === null) {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['academic_year_id' => __('Pick an academic year and term first.')]);
        }

        $payload = $request->validated();

        $meta = ReportCardMeta::query()->firstOrNew([
            'tenant_id' => $student->tenant_id,
            'student_id' => $student->id,
            'academic_year_id' => $year->id,
            'term_id' => $term->id,
        ]);

        $meta->fill($payload);
        $meta->save();

        return redirect()
            ->route('report-card-meta.edit', [
                'student' => $student->id,
                'academic_year_id' => $year->id,
                'term_id' => $term->id,
            ])
            ->with('status', __('Report card meta saved.'));
    }

    /**
     * Classes a user may pick from on the index screen.
     *
     * @return \Illuminate\Support\Collection<int, SchoolClass>
     */
    private function classesVisibleTo($user)
    {
        if ($user && $user->role === UserRole::Teacher->value) {
            return $user->assignedClasses()->orderBy('name')->orderBy('section')->get();
        }

        return SchoolClass::query()->orderBy('name')->orderBy('section')->get();
    }

    /**
     * @return array{0: ?AcademicYear, 1: ?Term}
     */
    private function resolvePeriod(Request $request): array
    {
        $yearId = $request->integer('academic_year_id');
        $termId = $request->integer('term_id');

        $year = $yearId
            ? AcademicYear::query()->whereKey($yearId)->first()
            : $this->academic->currentYear();
        $term = $termId
            ? Term::query()->whereKey($termId)->first()
            : $this->academic->currentTerm();

        return [$year, $term];
    }
}
