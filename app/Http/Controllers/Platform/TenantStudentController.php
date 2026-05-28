<?php

namespace App\Http\Controllers\Platform;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantStudentController extends Controller
{
    public function index(Request $request, Tenant $tenant): View
    {
        $this->authorize('platform.viewTenantStudents');

        $q = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->with(['schoolClass', 'linkedUser']);

        if ($request->filled('search')) {
            $s = '%'.$request->string('search')->trim().'%';
            $q->where(function ($w) use ($s): void {
                $w->where('name', 'like', $s)
                    ->orWhere('parent_name', 'like', $s)
                    ->orWhere('parent_phone', 'like', $s);
            });
        }

        if ($request->filled('class_id')) {
            $q->where('class_id', (int) $request->query('class_id'));
        }

        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }

        $students = $q->orderBy('name')->paginate(25)->withQueryString();

        $classes = SchoolClass::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('name')
            ->get();

        return view('platform.tenant-students.index', [
            'tenant' => $tenant,
            'students' => $students,
            'classes' => $classes,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'class_id' => $request->query('class_id'),
                'status' => $request->query('status'),
            ],
        ]);
    }

    public function show(Tenant $tenant, Student $student): View
    {
        $this->authorize('platform.viewTenantStudents');
        $this->assertTenantStudent($tenant, $student);

        $student->load(['schoolClass', 'guardians', 'linkedUser']);

        $tid = (int) $tenant->id;
        $expected = (float) Fee::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('class_id', $student->class_id)
            ->sum('amount');

        $paid = (float) Payment::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('student_id', $student->id)
            ->sum('amount');

        $parentAccounts = User::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('role', UserRole::Parent->value)
            ->whereHas('children', fn ($c) => $c->where('students.id', $student->id))
            ->get();

        $resultsCount = Result::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('student_id', $student->id)
            ->count();
        $attendanceCount = Attendance::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('student_id', $student->id)
            ->count();

        return view('platform.tenant-students.show', [
            'tenant' => $tenant,
            'student' => $student,
            'feeExpected' => $expected,
            'feePaid' => $paid,
            'feeBalance' => max(0, $expected - $paid),
            'parentAccounts' => $parentAccounts,
            'resultsCount' => $resultsCount,
            'attendanceCount' => $attendanceCount,
        ]);
    }

    private function assertTenantStudent(Tenant $tenant, Student $student): void
    {
        if ((int) $student->tenant_id !== (int) $tenant->id) {
            abort(404);
        }
    }
}
