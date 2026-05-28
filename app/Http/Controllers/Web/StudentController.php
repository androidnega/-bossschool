<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Services\PlanLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Student::class, 'student');
    }

    public function index(Request $request): View
    {
        $query = Student::query()->with('schoolClass');

        $user = $request->user();
        if ($user && $user->role === UserRole::Teacher->value) {
            $classIds = $user->assignedClasses()->pluck('classes.id');
            $query->whereIn('class_id', $classIds);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('parent_name', 'like', '%'.$search.'%')
                    ->orWhere('parent_phone', 'like', '%'.$search.'%');
            });
        }

        $classId = $request->query('class_id');
        if ($classId !== null && $classId !== '') {
            $query->where('class_id', (int) $classId);
        }

        $students = $query->orderBy('name')->paginate(15)->withQueryString();

        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('students.index', [
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'q' => $search,
                'class_id' => $classId !== null && $classId !== '' ? (string) $classId : '',
            ],
        ]);
    }

    public function create(): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('students.create', [
            'classes' => $classes,
        ]);
    }

    public function store(StoreStudentRequest $request, PlanLimits $limits): RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail((int) $request->user()->tenant_id);
        if (! $request->user()->isSuperAdmin() && ! $limits->canAddStudent($tenant)) {
            throw ValidationException::withMessages([
                'name' => __('Plan limit reached for students. Upgrade the plan to add more.'),
            ]);
        }

        Student::query()->create($request->validated());

        return redirect()->route('students.index')->with('status', __('Student created.'));
    }

    public function show(Student $student): View
    {
        $student->load('schoolClass');

        return view('students.show', compact('student'));
    }

    public function edit(Student $student): View
    {
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        return view('students.edit', compact('student', 'classes'));
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $student->update($request->validated());

        return redirect()->route('students.show', $student)->with('status', __('Student updated.'));
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()->route('students.index')->with('status', __('Student removed.'));
    }
}
