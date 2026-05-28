<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\ReportCardMeta\BulkUpdateReportCardMetaRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ReportCardMeta;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Policies\ReportCardMetaPolicy;
use App\Services\AcademicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Bulk helpers for report-card meta data.
 *
 * The standard flow (one student at a time) still lives on
 * ReportCardMetaController — this controller adds the "apply to every
 * student in the class" shortcut for class-level fields, plus the optional
 * prefill of attendance summaries from the attendance table.
 *
 * Teachers can only bulk-edit the subset of fields ReportCardMetaPolicy
 * allows them to (no head_teacher_remark, no next_term_fee, etc.) and only
 * for classes they are assigned to.
 */
class ReportCardBulkController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function edit(Request $request): View
    {
        $this->authorize('viewAny', ReportCardMeta::class);

        $user = $request->user();
        $classes = $this->classesVisibleTo($user);
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $terms = Term::query()->with('academicYear')->orderBy('term_order')->get();

        $classId = $request->integer('class_id') ?: $classes->first()?->id;
        $yearId = $request->integer('academic_year_id') ?: $this->academic->currentYear()?->id;
        $termId = $request->integer('term_id') ?: $this->academic->currentTerm()?->id;

        return view('report-card-meta.bulk', [
            'classes' => $classes,
            'years' => $years,
            'terms' => $terms,
            'classId' => $classId,
            'yearId' => $yearId,
            'termId' => $termId,
            'editableFields' => ReportCardMetaPolicy::editableFieldsFor($user),
        ]);
    }

    public function update(BulkUpdateReportCardMetaRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $tenantId = (int) $user->tenant_id;
        $classId = (int) $data['class_id'];
        $yearId = (int) $data['academic_year_id'];
        $termId = (int) $data['term_id'];
        $prefill = ! empty($data['prefill_attendance']);

        $editable = ReportCardMetaPolicy::editableFieldsFor($user);
        $payload = array_intersect_key($data, array_flip($editable));

        $students = Student::query()
            ->where('tenant_id', $tenantId)
            ->where('class_id', $classId)
            ->whereIn('status', ['active', 'inactive'])
            ->get();

        if ($students->isEmpty()) {
            return redirect()->route('report-card-meta.bulk.edit', compact('classId', 'yearId', 'termId'))
                ->with('status', __('No students found in this class.'));
        }

        // Pre-compute attendance summary per student so the per-row update
        // doesn't issue N+1 queries.
        $attendanceSummary = $prefill
            ? Attendance::query()
                ->where('tenant_id', $tenantId)
                ->where('term_id', $termId)
                ->whereIn('student_id', $students->pluck('id'))
                ->selectRaw('student_id, status, COUNT(*) as cnt')
                ->groupBy('student_id', 'status')
                ->get()
                ->groupBy('student_id')
            : collect();

        DB::transaction(function () use ($students, $payload, $tenantId, $yearId, $termId, $prefill, $attendanceSummary): void {
            foreach ($students as $student) {
                $meta = ReportCardMeta::query()->firstOrNew([
                    'tenant_id' => $tenantId,
                    'student_id' => $student->id,
                    'academic_year_id' => $yearId,
                    'term_id' => $termId,
                ]);

                foreach ($payload as $key => $value) {
                    if ($value === null || $value === '') {
                        continue;
                    }
                    $meta->{$key} = $value;
                }

                if ($prefill) {
                    $rows = $attendanceSummary->get($student->id, collect());
                    $present = (int) ($rows->firstWhere('status', Attendance::STATUS_PRESENT)?->cnt ?? 0);
                    $absent = (int) ($rows->firstWhere('status', Attendance::STATUS_ABSENT)?->cnt ?? 0);
                    $meta->days_present = $present;
                    $meta->days_absent = $absent;
                }

                $meta->save();
            }
        });

        return redirect()->route('report-card-meta.bulk.edit', compact('classId', 'yearId', 'termId'))
            ->with('status', __('Bulk update applied to :n students.', ['n' => $students->count()]));
    }

    /**
     * @return \Illuminate\Support\Collection<int, SchoolClass>
     */
    private function classesVisibleTo($user)
    {
        if ($user && $user->role === UserRole::Teacher->value) {
            return $user->assignedClasses()->orderBy('name')->orderBy('section')->get();
        }

        return SchoolClass::query()->orderBy('name')->orderBy('section')->get();
    }
}
