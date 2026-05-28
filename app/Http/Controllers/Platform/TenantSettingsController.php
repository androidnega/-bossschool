<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateTenantSchoolSettingsRequest;
use App\Models\School;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSettingsController extends Controller
{
    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.manageTenants');

        $tenant->load('school');

        return view('platform.tenant-settings.index', compact('tenant'));
    }

    public function update(UpdateTenantSchoolSettingsRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');

        $data = $request->validated();

        $tenant->update([
            'name' => $data['tenant_name'],
            'subdomain' => $data['subdomain'],
            'status' => $data['tenant_status'],
            'trial_end' => $data['tenant_status'] === Tenant::STATUS_TRIAL
                ? ($data['trial_end'] ?? $tenant->trial_end)
                : null,
        ]);

        School::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => $data['school_name'],
                'address' => $data['address'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'academic_year' => $data['academic_year'] ?? null,
            ]
        );

        return redirect()
            ->route('platform.tenants.settings.index', $tenant)
            ->with('status', __('School settings saved.'));
    }
}
