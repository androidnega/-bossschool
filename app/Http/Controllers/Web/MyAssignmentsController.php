<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only view of the signed-in teacher's own subject + class assignments.
 * Anybody else hits 403 — they would never need this page in the first
 * place. Admins/Proprietors manage assignments via TeacherSubjectAssignmentController.
 */
class MyAssignmentsController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user && $user->role === UserRole::Teacher->value, 403);

        return view('staff.assignments.mine', [
            'subjects' => $user->assignedSubjects()->orderBy('name')->get(),
            'classes' => $user->assignedClasses()->orderBy('name')->orderBy('section')->get(),
        ]);
    }
}
