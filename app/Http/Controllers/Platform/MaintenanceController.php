<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateMaintenanceRequest;
use App\Models\MaintenanceMode;
use App\Models\PlatformSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.manage');

        $global = MaintenanceMode::globalRow();

        return view('platform.maintenance.index', compact('global'));
    }

    public function update(UpdateMaintenanceRequest $request): RedirectResponse
    {
        $this->authorize('platform.manage');

        $data = $request->validated();
        $row = MaintenanceMode::query()->whereNull('tenant_id')->orderByDesc('id')->first();

        if ($row === null) {
            MaintenanceMode::query()->create([
                'tenant_id' => null,
                'is_enabled' => false,
                'message' => $data['message'] ?? null,
                'starts_at' => $data['starts_at'] ?? null,
                'ends_at' => $data['ends_at'] ?? null,
                'enabled_by' => $request->user()->id,
            ]);
        } else {
            $row->update([
                'message' => $data['message'] ?? $row->message,
                'starts_at' => $data['starts_at'] ?? $row->starts_at,
                'ends_at' => $data['ends_at'] ?? $row->ends_at,
            ]);
        }

        $this->activityLogger->log('settings_updated', 'Global maintenance window updated');

        return redirect()->route('platform.maintenance.index')->with('status', __('Maintenance settings saved.'));
    }

    public function enable(UpdateMaintenanceRequest $request): RedirectResponse
    {
        $this->authorize('platform.manage');
        $data = $request->validated();

        $row = MaintenanceMode::query()->whereNull('tenant_id')->orderByDesc('id')->first();
        if ($row === null) {
            MaintenanceMode::query()->create([
                'tenant_id' => null,
                'is_enabled' => true,
                'message' => $data['message'] ?? __('We are upgrading BossSchool. Please try again soon.'),
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['ends_at'] ?? null,
                'enabled_by' => $request->user()->id,
            ]);
        } else {
            $row->update([
                'is_enabled' => true,
                'message' => $data['message'] ?? $row->message,
                'starts_at' => $data['starts_at'] ?? now(),
                'ends_at' => $data['ends_at'] ?? $row->ends_at,
                'enabled_by' => $request->user()->id,
            ]);
        }

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'maintenance_enabled'],
            ['value' => '1', 'type' => 'bool', 'group' => 'maintenance']
        );
        PlatformSetting::forgetCache();

        $this->activityLogger->log('maintenance_enabled', 'Global maintenance enabled');

        return redirect()->route('platform.maintenance.index')->with('status', __('Maintenance enabled.'));
    }

    public function disable(): RedirectResponse
    {
        $this->authorize('platform.manage');

        MaintenanceMode::query()->whereNull('tenant_id')->update([
            'is_enabled' => false,
            'ends_at' => now(),
        ]);

        PlatformSetting::query()->updateOrCreate(
            ['key' => 'maintenance_enabled'],
            ['value' => '0', 'type' => 'bool', 'group' => 'maintenance']
        );
        PlatformSetting::forgetCache();

        $this->activityLogger->log('maintenance_disabled', 'Global maintenance disabled');

        return redirect()->route('platform.maintenance.index')->with('status', __('Maintenance disabled.'));
    }
}
