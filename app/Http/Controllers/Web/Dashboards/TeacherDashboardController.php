<?php

namespace App\Http\Controllers\Web\Dashboards;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Message;
use App\Models\Result;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $classes = $user->assignedClasses()->orderBy('name')->get();
        $subjects = $user->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        $classIds = $classes->pluck('id');
        $studentsInClasses = Student::query()->whereIn('class_id', $classIds)->count();

        $studentIdsToday = Student::query()->whereIn('class_id', $classIds)->pluck('id');

        $attendanceToday = Attendance::query()
            ->whereDate('date', Carbon::today())
            ->whereIn('student_id', $studentIdsToday)
            ->count();

        $absentToday = Attendance::query()
            ->whereDate('date', Carbon::today())
            ->where('status', 'absent')
            ->whereIn('student_id', $studentIdsToday)
            ->count();

        $subjectIds = $subjects->pluck('id');
        $resultsScope = Result::query()->whereIn('subject_id', $subjectIds);

        $resultsPending = (clone $resultsScope)
            ->whereRaw('(COALESCE(class_test,0)+COALESCE(midterm,0)+COALESCE(exam,0)) = 0')
            ->count();

        $resultsEntered = (clone $resultsScope)
            ->whereRaw('(COALESCE(class_test,0)+COALESCE(midterm,0)+COALESCE(exam,0)) > 0')
            ->count();

        $avgScore = (float) (clone $resultsScope)
            ->whereRaw('(COALESCE(class_test,0)+COALESCE(midterm,0)+COALESCE(exam,0)) > 0')
            ->selectRaw('AVG(COALESCE(class_test,0)+COALESCE(midterm,0)+COALESCE(exam,0)) as v')
            ->value('v');

        $recentReportStudents = Student::query()
            ->whereIn('class_id', $classIds)
            ->with('schoolClass')
            ->orderByDesc('updated_at')
            ->limit(5)
            ->get();

        $recentNotices = Message::query()
            ->with(['sender', 'schoolClass'])
            ->visibleToTeacher($user)
            ->limit(6)
            ->get();

        return view('dashboards.teacher', compact(
            'classes',
            'subjects',
            'studentsInClasses',
            'attendanceToday',
            'absentToday',
            'resultsPending',
            'resultsEntered',
            'avgScore',
            'recentReportStudents',
            'recentNotices'
        ));
    }
}
