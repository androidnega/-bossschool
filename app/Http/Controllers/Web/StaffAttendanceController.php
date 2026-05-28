<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\StaffAttendance\StoreStaffAttendanceRequest;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Services\AcademicContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Daily marking + report for staff attendance. Only Admin / Proprietor —
 * teachers and accountants don't mark staff attendance by default.
 *
 * The (tenant, staff, date) tuple is unique so re-submitting a date safely
 * upserts the row instead of creating duplicates.
 */
class StaffAttendanceController extends Controller
{
    public function __construct(private readonly AcademicContext $academic) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', StaffAttendance::class);

        $date = $request->input('date', Carbon::today()->toDateString());

        $rows = StaffAttendance::query()
            ->with(['staff', 'marker'])
            ->whereDate('date', $date)
            ->orderBy('staff_id')
            ->get();

        $report = StaffAttendance::query()
            ->selectRaw('status, COUNT(*) as c')
            ->whereDate('date', $date)
            ->groupBy('status')
            ->pluck('c', 'status');

        return view('staff-attendance.index', [
            'rows' => $rows,
            'report' => $report,
            'date' => $date,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', StaffAttendance::class);

        $date = $request->input('date', Carbon::today()->toDateString());
        $staff = Staff::query()->orderBy('name')->get();

        $existing = StaffAttendance::query()
            ->whereDate('date', $date)
            ->get()
            ->keyBy('staff_id');

        return view('staff-attendance.mark', [
            'staff' => $staff,
            'existing' => $existing,
            'date' => $date,
            'currentTerm' => $this->academic->currentTerm(),
            'currentYear' => $this->academic->currentYear(),
        ]);
    }

    public function store(StoreStaffAttendanceRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $tenantId = (int) $request->user()->tenant_id;
        $markerId = (int) $request->user()->id;
        $date = Carbon::parse($data['date'])->toDateString();

        $staffIds = Staff::query()->pluck('id')->all();

        DB::transaction(function () use ($data, $staffIds, $tenantId, $markerId, $date): void {
            foreach ($data['statuses'] as $staffId => $status) {
                $staffId = (int) $staffId;
                if (! in_array($staffId, $staffIds, true)) {
                    continue;
                }

                StaffAttendance::query()->updateOrCreate(
                    ['tenant_id' => $tenantId, 'staff_id' => $staffId, 'date' => $date],
                    [
                        'academic_year_id' => $data['academic_year_id'] ?? null,
                        'term_id' => $data['term_id'] ?? null,
                        'status' => $status,
                        'remarks' => $data['remarks'][$staffId] ?? null,
                        'marked_by_user_id' => $markerId,
                    ],
                );
            }
        });

        return redirect()
            ->route('staff-attendance.create', ['date' => $date])
            ->with('status', __('Staff attendance saved.'));
    }
}
