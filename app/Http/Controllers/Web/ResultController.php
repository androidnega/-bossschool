<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Result\StoreResultRequest;
use App\Http\Requests\Result\UpdateResultRequest;
use App\Models\AcademicYear;
use App\Models\Result;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use App\Services\AcademicContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function __construct(private readonly AcademicContext $academic)
    {
        $this->authorizeResource(Result::class, 'result', ['except' => ['show']]);
    }

    public function index(Request $request): View
    {
        $currentYear = $this->academic->currentYear();
        $currentTerm = $this->academic->currentTerm();

        $yearId = $request->integer('academic_year_id') ?: $currentYear?->id;
        $termId = $request->integer('term_id') ?: $currentTerm?->id;

        $query = Result::query()
            ->with(['student', 'subject.schoolClass', 'term', 'academicYear'])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->when($termId, fn ($q) => $q->where('term_id', $termId))
            ->orderByDesc('id');

        $user = $request->user();
        if ($user && $user->role === UserRole::Teacher->value) {
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $query->whereIn('subject_id', $subjectIds);
        }

        $results = $query->paginate(25)->withQueryString();

        return view('results.index', [
            'results' => $results,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get(),
            'terms' => Term::query()->with('academicYear')->orderByDesc('term_order')->get(),
            'yearId' => $yearId,
            'termId' => $termId,
            'currentYear' => $currentYear,
            'currentTerm' => $currentTerm,
        ]);
    }

    public function create(Request $request): View
    {
        $students = Student::query()->with('schoolClass')->orderBy('name')->get();
        $subjects = Subject::query()->with('schoolClass')->orderBy('name')->get();

        $user = $request->user();
        if ($user && $user->role === UserRole::Teacher->value) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $students = Student::query()->with('schoolClass')->whereIn('class_id', $classIds)->orderBy('name')->get();
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $subjects = Subject::query()->with('schoolClass')->whereIn('id', $subjectIds)->orderBy('name')->get();
        }

        return view('results.create', [
            'students' => $students,
            'subjects' => $subjects,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get(),
            'terms' => Term::query()->with('academicYear')->orderBy('term_order')->get(),
            'currentYear' => $this->academic->currentYear(),
            'currentTerm' => $this->academic->currentTerm(),
            'canOverrideTerm' => in_array($user?->role, [UserRole::Admin->value, UserRole::Proprietor->value], true),
        ]);
    }

    public function store(StoreResultRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // If a soft-deleted result for the same (student, subject, year, term)
        // exists, restore + update it to avoid unique-index collisions.
        $existing = Result::query()
            ->withTrashed()
            ->where('tenant_id', (int) $request->user()->tenant_id)
            ->where('student_id', (int) $data['student_id'])
            ->where('subject_id', (int) $data['subject_id'])
            ->where('academic_year_id', (int) $data['academic_year_id'])
            ->where('term_id', (int) $data['term_id'])
            ->first();

        if ($existing && $existing->trashed()) {
            $existing->restore();
            $existing->fill($data);
            $existing->save();
        } else {
            Result::query()->create($data);
        }

        return redirect()->route('results.index')->with('status', __('Result saved.'));
    }

    public function edit(Request $request, Result $result): View
    {
        $result->load(['student', 'subject']);
        $students = Student::query()->with('schoolClass')->orderBy('name')->get();
        $subjects = Subject::query()->with('schoolClass')->orderBy('name')->get();

        $user = $request->user();
        if ($user && $user->role === UserRole::Teacher->value) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $students = Student::query()->with('schoolClass')->whereIn('class_id', $classIds)->orderBy('name')->get();
            $subjectIds = $user->assignedSubjects()->pluck('subjects.id');
            $subjects = Subject::query()->with('schoolClass')->whereIn('id', $subjectIds)->orderBy('name')->get();
        }

        return view('results.edit', [
            'result' => $result,
            'students' => $students,
            'subjects' => $subjects,
            'years' => AcademicYear::query()->orderByDesc('starts_on')->orderByDesc('id')->get(),
            'terms' => Term::query()->with('academicYear')->orderBy('term_order')->get(),
            'canOverrideTerm' => in_array($user?->role, [UserRole::Admin->value, UserRole::Proprietor->value], true),
        ]);
    }

    public function update(UpdateResultRequest $request, Result $result): RedirectResponse
    {
        $result->update($request->validated());

        return redirect()->route('results.index')->with('status', __('Result updated.'));
    }

    public function destroy(Result $result): RedirectResponse
    {
        $result->delete();

        return redirect()->route('results.index')->with('status', __('Result removed.'));
    }
}
