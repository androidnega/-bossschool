<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\View\View;

class TenantAttendanceController extends Controller
{
    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.view');

        $tid = (int) $tenant->id;
        $today = Carbon::today()->toDateString();

        $todayRows = Attendance::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->where('date', $today);

        $present = (clone $todayRows)->where('status', 'present')->count();
        $absent = (clone $todayRows)->where('status', 'absent')->count();
        $late = (clone $todayRows)->where('status', 'late')->count();
        $excused = (clone $todayRows)->where('status', 'excused')->count();

        $recent = Attendance::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->with('student')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(25)
            ->get();

        return view('platform.tenant-attendance.index', compact(
            'tenant',
            'today',
            'present',
            'absent',
            'late',
            'excused',
            'recent'
        ));
    }
}
