<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\Staff;
use App\Models\Tenant;
use App\Services\PlanLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function index(): View
    {
        $this->authorize('staff.view');

        $staff = Staff::query()->orderBy('name')->paginate(25);

        return view('staff.index', compact('staff'));
    }

    public function create(): View
    {
        $this->authorize('staff.manage');

        return view('staff.create');
    }

    public function store(StoreStaffRequest $request, PlanLimits $limits): RedirectResponse
    {
        $tenant = Tenant::query()->findOrFail((int) $request->user()->tenant_id);
        if (! $request->user()->isSuperAdmin() && ! $limits->canAddStaff($tenant)) {
            throw ValidationException::withMessages([
                'name' => __('Plan limit reached for staff. Upgrade the plan to add more.'),
            ]);
        }

        Staff::query()->create([
            'tenant_id' => (int) $request->user()->tenant_id,
            ...$request->validated(),
        ]);

        return redirect()->route('staff.index')->with('status', __('Staff member added.'));
    }

    public function edit(Staff $staff): View
    {
        $this->authorize('staff.manage');
        $this->ensureSameTenant($staff);

        return view('staff.edit', compact('staff'));
    }

    public function update(UpdateStaffRequest $request, Staff $staff): RedirectResponse
    {
        $this->authorize('staff.manage');
        $this->ensureSameTenant($staff);

        $staff->update($request->validated());

        return redirect()->route('staff.index')->with('status', __('Staff member updated.'));
    }

    public function destroy(Staff $staff): RedirectResponse
    {
        $this->authorize('staff.manage');
        $this->ensureSameTenant($staff);

        $staff->delete();

        return redirect()->route('staff.index')->with('status', __('Staff member removed.'));
    }

    private function ensureSameTenant(Staff $staff): void
    {
        $user = request()->user();
        if (! $user || (int) $staff->tenant_id !== (int) $user->tenant_id) {
            abort(403);
        }
    }
}
