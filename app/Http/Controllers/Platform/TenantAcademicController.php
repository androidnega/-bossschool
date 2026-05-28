<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Tenant;
use App\Services\Platform\TenantMetricsService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantAcademicController extends Controller
{
    public function __construct(
        private TenantMetricsService $metrics
    ) {}

    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.view');

        $tid = (int) $tenant->id;

        $classes = SchoolClass::withoutGlobalScopes()->where('tenant_id', $tid)->orderBy('name')->get();
        $subjectsCount = Subject::withoutGlobalScopes()->where('tenant_id', $tid)->count();
        $resultsCount = Result::withoutGlobalScopes()->where('tenant_id', $tid)->count();

        $averages = $this->metrics->averageScoresBySubject($tid);
        $studentsWithoutResults = $this->metrics->studentsWithoutResultsCount($tid);

        $activeStudents = Student::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('status', 'active')
            ->count();

        $withResults = $activeStudents - $studentsWithoutResults;
        $readinessPct = $activeStudents > 0 ? round(100 * max(0, $withResults) / $activeStudents) : 0;

        $subjectsByClass = Subject::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->select('class_id', DB::raw('COUNT(*) as c'))
            ->groupBy('class_id')
            ->pluck('c', 'class_id');

        return view('platform.tenant-academics.index', compact(
            'tenant',
            'classes',
            'subjectsCount',
            'resultsCount',
            'averages',
            'studentsWithoutResults',
            'readinessPct',
            'subjectsByClass'
        ));
    }
}
