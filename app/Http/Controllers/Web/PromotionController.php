<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\PromotionHistory;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\AcademicContext;
use App\Services\PromotionPreviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function __construct(
        private readonly AcademicContext $academic,
        private readonly PromotionPreviewService $preview,
    ) {
        $this->middleware(function (Request $request, $next) {
            $user = $request->user();
            if (! $user || ! in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
                abort(403);
            }

            return $next($request);
        });
    }

    public function index(Request $request): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $terms = Term::query()->with('academicYear')->orderBy('term_order')->get();

        $fromClassId = $request->integer('from_class_id') ?: $classes->first()?->id;
        $fromYearId = $request->integer('from_academic_year_id') ?: $this->academic->currentYear()?->id;
        $toClassId = $request->integer('to_class_id');
        $toYearId = $request->integer('to_academic_year_id') ?: $fromYearId;

        // Default the preview term to Term 3 if it exists in the chosen year,
        // otherwise fall back to the current term.
        $termId = $request->integer('term_id');
        if (! $termId && $fromYearId) {
            $term3 = Term::query()
                ->where('academic_year_id', $fromYearId)
                ->where('term_order', 3)
                ->first();
            $termId = $term3?->id ?? $this->academic->currentTerm()?->id;
        }

        $fromClass = $fromClassId ? SchoolClass::query()->whereKey($fromClassId)->first() : null;
        $fromYear = $fromYearId ? AcademicYear::query()->whereKey($fromYearId)->first() : null;
        $previewTerm = $termId ? Term::query()->whereKey($termId)->first() : null;

        $rows = collect();
        $tenantSettings = app(\App\Services\TenantSettings::class);
        $tenantId = (int) ($request->user()?->tenant_id ?? 0);
        $configPassMark = (float) config('schools.score.pass_mark', PromotionPreviewService::DEFAULT_PASS_MARK);
        // Tenant setting wins; falls back to the platform-wide config value.
        $passMark = (float) ($tenantId ? $tenantSettings->get($tenantId, 'default_pass_mark', $configPassMark) : $configPassMark);
        if ($fromClass && $fromYear && $previewTerm) {
            $isFinalYearClass = $this->looksLikeFinalYear($fromClass, $classes);
            $rows = $this->preview->for($fromClass, $fromYear, $previewTerm, $isFinalYearClass, $passMark);
        }

        $history = PromotionHistory::query()
            ->with(['student', 'fromClass', 'toClass', 'fromAcademicYear', 'toAcademicYear', 'promoter'])
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('promotions.index', [
            'classes' => $classes,
            'years' => $years,
            'terms' => $terms,
            'rows' => $rows,
            'fromClassId' => $fromClassId,
            'toClassId' => $toClassId,
            'fromYearId' => $fromYearId,
            'toYearId' => $toYearId,
            'termId' => $termId,
            'history' => $history,
            'passMark' => $passMark,
        ]);
    }

    /**
     * Heuristic: a class looks "final year" if it's named JHS 3 or similar, or
     * if its name is the alphabetically/numerically last among the tenant's
     * classes. The proprietor can always override the suggested action.
     */
    private function looksLikeFinalYear(SchoolClass $class, $classes): bool
    {
        $name = strtolower(trim((string) $class->name));
        foreach (['jhs 3', 'jhs3', 'form 3', 'shs 3', 'shs3'] as $needle) {
            if (str_contains($name, $needle)) {
                return true;
            }
        }

        return false;
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = (int) $request->user()->tenant_id;
        $data = $request->validate([
            'from_class_id' => ['required', Rule::exists('classes', 'id')->where('tenant_id', $tenantId)],
            'to_class_id' => ['nullable', Rule::exists('classes', 'id')->where('tenant_id', $tenantId)],
            'from_academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('tenant_id', $tenantId)],
            'to_academic_year_id' => ['required', Rule::exists('academic_years', 'id')->where('tenant_id', $tenantId)],
            'status' => ['required', Rule::in(PromotionHistory::STATUSES)],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->where('tenant_id', $tenantId)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Sanity: cannot graduate without leaving to_class_id null and
        // cannot promote without a destination class.
        if ($data['status'] === PromotionHistory::STATUS_PROMOTED && empty($data['to_class_id'])) {
            return redirect()->back()->withInput()->withErrors(['to_class_id' => __('Choose the destination class to promote into.')]);
        }

        $studentIds = collect($data['student_ids'])->map(fn ($id) => (int) $id)->all();

        DB::transaction(function () use ($data, $studentIds, $tenantId, $request): void {
            foreach ($studentIds as $studentId) {
                $student = Student::query()->whereKey($studentId)->first();
                if ($student === null || (int) $student->class_id !== (int) $data['from_class_id']) {
                    continue; // Refuse to touch students not in the from-class.
                }

                $previousClassId = $student->class_id;

                PromotionHistory::query()->create([
                    'tenant_id' => $tenantId,
                    'student_id' => $student->id,
                    'from_class_id' => $previousClassId,
                    'to_class_id' => $data['status'] === PromotionHistory::STATUS_GRADUATED ? null : ($data['to_class_id'] ?? null),
                    'from_academic_year_id' => $data['from_academic_year_id'],
                    'to_academic_year_id' => $data['to_academic_year_id'],
                    'promoted_by_user_id' => (int) $request->user()->id,
                    'status' => $data['status'],
                    'notes' => $data['notes'] ?? null,
                ]);

                if ($data['status'] === PromotionHistory::STATUS_PROMOTED && ! empty($data['to_class_id'])) {
                    $student->class_id = (int) $data['to_class_id'];
                    $student->save();
                } elseif ($data['status'] === PromotionHistory::STATUS_GRADUATED) {
                    $student->status = 'graduated';
                    $student->save();
                }
                // STATUS_REPEATED: keep the student in the same class.
            }
        });

        return redirect()
            ->route('promotions.index')
            ->with('status', __('Promotion recorded.'));
    }
}
