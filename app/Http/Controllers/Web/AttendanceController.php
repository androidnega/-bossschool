<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\StoreAttendanceRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Term;
use App\Services\AbsenceAlertService;
use App\Services\AcademicContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $user = $request->user();
        $classQuery = SchoolClass::query()->orderBy('name');

        if ($user->role === UserRole::Teacher->value) {
            $classQuery->whereIn('id', $user->assignedClasses()->pluck('classes.id'));
        }

        $classes = $classQuery->get();
        $years = AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get();
        $terms = Term::query()->with('academicYear')->orderByDesc('term_order')->get();
        $currentTerm = $this->academic->currentTerm();
        $currentYear = $this->academic->currentYear();

        $classId = $request->integer('class_id') ?: $classes->first()?->id;
        $termId = $request->integer('term_id') ?: $currentTerm?->id;
        $date = $request->input('date');

        $entries = collect();
        $selectedClass = null;
        if ($classId) {
            $selectedClass = $classes->firstWhere('id', $classId);
            if ($selectedClass) {
                $entries = Attendance::query()
                    ->with(['student.schoolClass', 'marker'])
                    ->whereHas('student', fn ($q) => $q->where('class_id', $classId))
                    ->when($termId, fn ($q) => $q->where('term_id', $termId))
                    ->when($date, fn ($q) => $q->whereDate('date', $date))
                    ->orderByDesc('date')
                    ->orderBy('student_id')
                    ->limit(500)
                    ->get();
            }
        }

        return view('attendance.index', [
            'classes' => $classes,
            'years' => $years,
            'terms' => $terms,
            'entries' => $entries,
            'classId' => $classId,
            'termId' => $termId,
            'date' => $date,
            'selectedClass' => $selectedClass,
            'currentTerm' => $currentTerm,
            'currentYear' => $currentYear,
        ]);
    }

    public function create(Request $request, SchoolClass $schoolClass): View
    {
        $this->authorize('markForClass', [Attendance::class, $schoolClass]);

        $currentTerm = $this->academic->currentTerm();
        $currentYear = $this->academic->currentYear();
        $date = $request->input('date', Carbon::today()->toDateString());

        $students = Student::query()
            ->where('class_id', $schoolClass->id)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Pre-fill from existing entries if any for that (class, date, term).
        $existing = Attendance::query()
            ->whereIn('student_id', $students->pluck('id'))
            ->whereDate('date', $date)
            ->when($currentTerm, fn ($q) => $q->where('term_id', $currentTerm->id))
            ->get()
            ->keyBy('student_id');

        return view('attendance.mark', [
            'class' => $schoolClass,
            'students' => $students,
            'existing' => $existing,
            'date' => $date,
            'currentTerm' => $currentTerm,
            'currentYear' => $currentYear,
        ]);
    }

    public function store(StoreAttendanceRequest $request, SchoolClass $schoolClass, AbsenceAlertService $alerts): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = (int) $request->user()->tenant_id;
        $markerId = (int) $request->user()->id;

        $studentIds = Student::query()
            ->where('class_id', $schoolClass->id)
            ->pluck('id')
            ->all();

        $date = Carbon::parse($data['date'])->toDateString();

        DB::transaction(function () use ($data, $studentIds, $tenantId, $markerId, $date): void {
            foreach ($data['statuses'] as $studentId => $status) {
                $studentId = (int) $studentId;
                if (! in_array($studentId, $studentIds, true)) {
                    continue; // Refuse to mark students outside this class.
                }

                $existing = Attendance::query()
                    ->where('tenant_id', $tenantId)
                    ->where('student_id', $studentId)
                    ->whereDate('date', $date)
                    ->first();

                if ($existing !== null) {
                    $existing->fill([
                        'academic_year_id' => $data['academic_year_id'],
                        'term_id' => $data['term_id'],
                        'status' => $status,
                        'remarks' => $data['remarks'][$studentId] ?? null,
                        'marked_by_user_id' => $markerId,
                    ])->save();
                    continue;
                }

                Attendance::query()->create([
                    'tenant_id' => $tenantId,
                    'student_id' => $studentId,
                    'date' => $date,
                    'academic_year_id' => $data['academic_year_id'],
                    'term_id' => $data['term_id'],
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                    'marked_by_user_id' => $markerId,
                ]);
            }
        });

        $sendAlerts = $request->boolean('send_absence_sms', false);
        $alertSummary = ['queued' => 0, 'sent' => 0];
        if ($sendAlerts) {
            $absentRows = Attendance::query()
                ->with('student')
                ->where('tenant_id', $tenantId)
                ->whereIn('student_id', $studentIds)
                ->whereDate('date', $date)
                ->where('status', Attendance::STATUS_ABSENT)
                ->get();
            $alertSummary = $alerts->alertAbsences($absentRows, send: true);
        }

        $status = __('Attendance saved.');
        if ($sendAlerts && $alertSummary['queued'] > 0) {
            $status .= ' '.__(':sent of :queued SMS alerts sent.', $alertSummary);
        }

        return redirect()
            ->route('attendance.create', ['schoolClass' => $schoolClass, 'date' => $data['date']])
            ->with('status', $status);
    }
}
