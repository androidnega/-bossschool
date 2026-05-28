<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateTenantSettingsRequest;
use App\Services\TenantSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Per-school configuration that doesn't belong on the School profile row:
 * default pass mark, SMS provider, online payment toggles, etc. Stored in
 * tenant_settings via the TenantSettings service.
 *
 * Only Admin / Proprietor see this screen; the request class enforces it.
 */
class TenantSettingsController extends Controller
{
    public function index(TenantSettings $settings): View
    {
        $this->authorize('settings.manage');
        $tenant = app('currentTenant');
        $values = array_merge(TenantSettings::DEFAULTS, $settings->all($tenant->id));

        return view('settings.tenant', [
            'values' => $values,
        ]);
    }

    public function update(UpdateTenantSettingsRequest $request, TenantSettings $settings): RedirectResponse
    {
        $tenant = app('currentTenant');
        $payload = $request->validated();
        // Only known managed keys are persisted (TenantSettings::setMany
        // filters by MANAGED_KEYS).
        $settings->setMany($tenant->id, $payload);

        return redirect()->route('settings.tenant')->with('status', __('Tenant settings updated.'));
    }
}
