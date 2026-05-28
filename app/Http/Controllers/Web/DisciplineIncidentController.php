<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Discipline\StoreDisciplineIncidentRequest;
use App\Http\Requests\Discipline\UpdateDisciplineIncidentRequest;
use App\Models\DisciplineIncident;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\AcademicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisciplineIncidentController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', DisciplineIncident::class);

        $user = $request->user();
        $query = DisciplineIncident::query()
            ->with(['student.schoolClass', 'reporter', 'resolver'])
            ->orderByDesc('incident_date');

        if ($user->role === UserRole::Teacher->value) {
            // Limit to incidents involving students in the teacher's classes.
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $studentIds = Student::query()->whereIn('class_id', $classIds)->pluck('id');
            $query->whereIn('student_id', $studentIds);
        }

        if ($user->role === UserRole::Parent->value) {
            $studentIds = $user->children()->pluck('students.id');
            $query->whereIn('student_id', $studentIds);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('severity')) {
            $query->where('severity', $request->string('severity'));
        }

        $incidents = $query->paginate(20)->withQueryString();

        return view('discipline.index', [
            'incidents' => $incidents,
            'statuses' => DisciplineIncident::STATUSES,
            'severities' => DisciplineIncident::SEVERITIES,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', DisciplineIncident::class);

        $user = $request->user();
        $studentsQuery = Student::query()->whereIn('status', ['active', 'inactive'])->orderBy('name');

        if ($user->role === UserRole::Teacher->value) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $studentsQuery->whereIn('class_id', $classIds);
        }

        return view('discipline.create', [
            'students' => $studentsQuery->get(),
            'severities' => DisciplineIncident::SEVERITIES,
            'currentTerm' => $this->academic->currentTerm(),
            'currentYear' => $this->academic->currentYear(),
        ]);
    }

    public function store(StoreDisciplineIncidentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = (int) $request->user()->tenant_id;

        $student = Student::query()->whereKey($data['student_id'])->firstOrFail();
        abort_unless((int) $student->tenant_id === $tenantId, 403);

        $incident = DisciplineIncident::query()->create([
            'tenant_id' => $tenantId,
            'student_id' => $student->id,
            'academic_year_id' => $data['academic_year_id'] ?? $this->academic->currentYear()?->id,
            'term_id' => $data['term_id'] ?? $this->academic->currentTerm()?->id,
            'reported_by_user_id' => $request->user()->id,
            'incident_date' => $data['incident_date'],
            'category' => $data['category'],
            'description' => $data['description'],
            'action_taken' => $data['action_taken'] ?? null,
            'parent_notified' => $data['parent_notified'] ?? false,
            'severity' => $data['severity'],
            'status' => DisciplineIncident::STATUS_OPEN,
        ]);

        return redirect()->route('discipline.show', $incident)->with('status', __('Incident recorded.'));
    }

    public function show(Request $request, DisciplineIncident $disciplineIncident): View
    {
        $this->authorize('view', $disciplineIncident);

        return view('discipline.show', [
            'incident' => $disciplineIncident->load(['student.schoolClass', 'reporter', 'resolver']),
        ]);
    }

    public function update(UpdateDisciplineIncidentRequest $request, DisciplineIncident $disciplineIncident): RedirectResponse
    {
        $data = $request->validated();

        if (! empty($data['action_taken'])) {
            $disciplineIncident->action_taken = $data['action_taken'];
        }
        if (array_key_exists('parent_notified', $data)) {
            $disciplineIncident->parent_notified = (bool) $data['parent_notified'];
        }
        if (! empty($data['severity'])) {
            $disciplineIncident->severity = $data['severity'];
        }

        // Only admins/proprietors can resolve / escalate.
        if (! empty($data['status']) && $request->user()->can('resolve', $disciplineIncident)) {
            $disciplineIncident->status = $data['status'];
            if ($data['status'] === DisciplineIncident::STATUS_RESOLVED) {
                $disciplineIncident->resolved_at = now();
                $disciplineIncident->resolved_by_user_id = $request->user()->id;
            }
        }

        $disciplineIncident->save();

        return redirect()->route('discipline.show', $disciplineIncident)->with('status', __('Incident updated.'));
    }
}
