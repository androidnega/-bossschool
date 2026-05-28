<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StoreTenantRequest;
use App\Models\MaintenanceMode;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\SchoolTemplate;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use App\Services\TenantProvisioningService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.manageTenants');

        $tenants = Tenant::query()
            ->with('plan')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('platform.tenants.index', compact('tenants'));
    }

    public function create(): View
    {
        $this->authorize('platform.manageTenants');

        $plans = Plan::query()->orderByRaw('sort_order IS NULL, sort_order')->orderBy('name')->get();
        $templates = SchoolTemplate::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('platform.tenants.create', compact('plans', 'templates'));
    }

    public function store(StoreTenantRequest $request, TenantProvisioningService $provisioning): RedirectResponse
    {
        $data = $request->validated();

        // Rich path: an admin email is supplied → full provisioning with
        // optional school-setup template.
        if (! empty($data['admin_email'])) {
            $result = $provisioning->provision([
                'name' => $data['name'],
                'subdomain' => $data['subdomain'],
                'plan_id' => $data['plan_id'] ?? null,
                'status' => $data['status'],
                'admin_name' => $data['admin_name'] ?? null,
                'admin_email' => $data['admin_email'],
                'school_template_id' => $data['school_template_id'] ?? null,
                'school_template_code' => $data['school_template_code'] ?? null,
                'academic_year_name' => $data['academic_year_name'] ?? null,
                'include_kg' => (bool) ($data['include_kg'] ?? true),
                'create_default_fees' => (bool) ($data['create_default_fees'] ?? false),
                'create_default_classes' => (bool) ($data['create_default_classes'] ?? false),
                'create_demo_data' => (bool) ($data['create_demo_data'] ?? false),
                'create_demo_users' => (bool) ($data['create_demo_users'] ?? false),
                'send_email' => true,
            ]);

            $message = $result['template_summary']
                ? __('School created successfully with the selected Ghanaian basic school template.')
                : __('School provisioned. Credentials file: :file', ['file' => $result['credentials_file']]);

            return redirect()
                ->route('platform.tenants.show', $result['tenant'])
                ->with('status', $message);
        }

        $trialDays = PlatformSetting::getInt('default_trial_days', 14);

        $tenant = Tenant::query()->create([
            'name' => $data['name'],
            'subdomain' => $data['subdomain'],
            'plan_id' => $data['plan_id'] ?? null,
            'status' => $data['status'] === 'suspended' ? Tenant::STATUS_SUSPENDED : ($data['status'] === 'trial' ? Tenant::STATUS_TRIAL : Tenant::STATUS_ACTIVE),
            'trial_end' => $data['status'] === 'trial' ? now()->addDays($trialDays) : null,
        ]);

        $this->activityLogger->log(
            'tenant_created',
            'Tenant registered: '.$tenant->name,
            ['subdomain' => $tenant->subdomain, 'status' => $tenant->status],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return redirect()->route('platform.tenants.show', $tenant)->with('status', __('School created.'));
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');
        $tenant->update(['status' => Tenant::STATUS_SUSPENDED]);

        $this->activityLogger->log(
            'tenant_suspended',
            'Tenant suspended: '.$tenant->name,
            [],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return back()->with('status', __('School suspended.'));
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');
        $tenant->update(['status' => Tenant::STATUS_ACTIVE]);

        $this->activityLogger->log(
            'tenant_activated',
            'Tenant activated: '.$tenant->name,
            [],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return back()->with('status', __('School activated.'));
    }

    public function destroy(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');

        $request->validate([
            'confirm' => ['required', 'string', 'in:DELETE TENANT'],
        ]);

        $name = $tenant->name;
        $id = $tenant->id;

        DB::transaction(function () use ($tenant): void {
            $tenant->delete();
        });

        $this->activityLogger->log(
            'tenant_deleted',
            'Tenant deleted (soft): '.$name,
            ['tenant_id' => $id],
            null,
            Tenant::class,
            $id
        );

        return redirect()->route('platform.tenants.index')->with('status', __('School removed from active directory.'));
    }

    public function enableMaintenance(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');

        $data = $request->validate([
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        MaintenanceMode::query()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'is_enabled' => true,
                'message' => $data['message'] ?? __('This school is temporarily unavailable.'),
                'starts_at' => now(),
                'ends_at' => null,
                'enabled_by' => $request->user()->id,
            ]
        );

        $this->activityLogger->log(
            'maintenance_enabled',
            'Tenant maintenance enabled',
            ['tenant_id' => $tenant->id],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return back()->with('status', __('Tenant maintenance enabled.'));
    }

    public function disableMaintenance(Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenants');

        MaintenanceMode::query()->where('tenant_id', $tenant->id)->update([
            'is_enabled' => false,
            'ends_at' => now(),
        ]);

        $this->activityLogger->log(
            'maintenance_disabled',
            'Tenant maintenance disabled',
            ['tenant_id' => $tenant->id],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return back()->with('status', __('Tenant maintenance disabled.'));
    }
}
