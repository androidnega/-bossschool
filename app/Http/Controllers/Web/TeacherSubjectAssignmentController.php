<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * UI for managing the teacher_subject and teacher_class pivots that drive
 * teacher result-entry scope. Admin / Proprietor can view and edit every
 * teacher; teachers can read their own assignments but not edit them.
 *
 * "Removing" an assignment is purely a permission change — the
 * teacher_subject pivot is detached, but historic Result rows the teacher
 * authored remain intact. This is enforced by the absence of any cascade
 * on Result rows in the schema.
 */
class TeacherSubjectAssignmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('manageAssignments', User::class);

        $tenantId = (int) $request->user()->tenant_id;
        $teachers = User::query()
            ->where('tenant_id', $tenantId)
            ->where('role', UserRole::Teacher->value)
            ->orderBy('name')
            ->get();

        $selectedTeacherId = $request->integer('teacher_id') ?: $teachers->first()?->id;
        $selectedTeacher = $selectedTeacherId
            ? $teachers->firstWhere('id', $selectedTeacherId)
            : null;

        $subjects = Subject::query()->orderBy('name')->get();
        $classes = SchoolClass::query()->orderBy('name')->orderBy('section')->get();

        $assignedSubjectIds = collect();
        $assignedClassIds = collect();
        if ($selectedTeacher) {
            $assignedSubjectIds = $selectedTeacher->assignedSubjects()->pluck('subjects.id');
            $assignedClassIds = $selectedTeacher->assignedClasses()->pluck('classes.id');
        }

        return view('staff.assignments.index', [
            'teachers' => $teachers,
            'teacher' => $selectedTeacher,
            'subjects' => $subjects,
            'classes' => $classes,
            'assignedSubjectIds' => $assignedSubjectIds,
            'assignedClassIds' => $assignedClassIds,
        ]);
    }

    /**
     * Replace the teacher's assignment set in a single transaction. Sync
     * keeps the pivot accurate (additions + removals) without re-creating
     * unchanged rows. Old Result rows are untouched even if a subject is
     * detached, because permissions check live assignments, not historical
     * pivot membership.
     */
    public function update(Request $request, User $user, ActivityLogger $logger): RedirectResponse
    {
        $this->authorize('manageAssignments', User::class);
        abort_unless((int) $user->tenant_id === (int) $request->user()->tenant_id, 403);
        abort_unless($user->role === UserRole::Teacher->value, 422, 'Only Teacher users have subject assignments.');

        $data = $request->validate([
            'subject_ids' => ['nullable', 'array'],
            'subject_ids.*' => ['integer', 'exists:subjects,id'],
            'class_ids' => ['nullable', 'array'],
            'class_ids.*' => ['integer', 'exists:classes,id'],
        ]);

        $tenantId = (int) $request->user()->tenant_id;
        $subjectIds = $data['subject_ids'] ?? [];
        $classIds = $data['class_ids'] ?? [];

        DB::transaction(function () use ($user, $subjectIds, $classIds, $tenantId): void {
            $subjectSync = collect($subjectIds)->mapWithKeys(fn ($id) => [(int) $id => ['tenant_id' => $tenantId]])->toArray();
            $classSync = collect($classIds)->mapWithKeys(fn ($id) => [(int) $id => ['tenant_id' => $tenantId]])->toArray();
            $user->assignedSubjects()->sync($subjectSync);
            $user->assignedClasses()->sync($classSync);
        });

        $logger->log(
            'teacher_assignments_updated',
            'Updated subject/class assignments for teacher '.$user->name,
            [
                'teacher_id' => $user->id,
                'subject_ids' => $subjectIds,
                'class_ids' => $classIds,
            ],
            $tenantId,
            User::class,
            $user->id,
        );

        return redirect()
            ->route('staff.assignments.index', ['teacher_id' => $user->id])
            ->with('status', __('Assignments updated.'));
    }
}
