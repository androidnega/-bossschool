<?php

namespace App\Http\Controllers\Web\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Message;
use App\Models\SchoolClass;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        $studentCount = Student::query()->count();
        $newAdmissions = Student::query()->where('admission_date', '>=', Carbon::now()->subDays(30))->count();
        $classCount = SchoolClass::query()->count();
        $staffCount = Staff::query()->count();
        $attendanceToday = Attendance::query()->whereDate('date', Carbon::today())->count();
        $absentToday = Attendance::query()
            ->whereDate('date', Carbon::today())
            ->where('status', 'absent')
            ->count();
        $pendingInactive = Student::query()->where('status', '!=', 'active')->count();
        $recentStudents = Student::query()->with('schoolClass')->orderByDesc('id')->limit(6)->get();

        $recentMessages = Message::query()
            ->with(['sender', 'schoolClass'])
            ->recentForAdminDashboard()
            ->limit(6)
            ->get();

        $setupReminders = [];
        if ($classCount === 0) {
            $setupReminders[] = __('Add at least one class before enrolling students broadly.');
        }
        if (Term::query()->count() === 0) {
            $setupReminders[] = __('Create academic terms for fees and reporting.');
        }
        if (Subject::query()->count() === 0) {
            $setupReminders[] = __('Add subjects per class for results and report cards.');
        }

        return view('dashboards.admin', compact(
            'studentCount',
            'newAdmissions',
            'classCount',
            'staffCount',
            'attendanceToday',
            'absentToday',
            'pendingInactive',
            'recentStudents',
            'recentMessages',
            'setupReminders'
        ));
    }
}
