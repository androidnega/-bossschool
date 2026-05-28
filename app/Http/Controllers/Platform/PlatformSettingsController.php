<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdatePlatformAppSettingsRequest;
use App\Models\MaintenanceMode;
use App\Models\PlatformSetting;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformSettingsController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.manage');

        $settings = PlatformSetting::allCached();

        return view('platform.settings.index', compact('settings'));
    }

    public function update(UpdatePlatformAppSettingsRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $rows = [
            'platform_name' => ['value' => $data['platform_name'], 'type' => 'string', 'group' => 'general'],
            'support_email' => ['value' => $data['support_email'], 'type' => 'string', 'group' => 'general'],
            'support_phone' => ['value' => $data['support_phone'] ?? '', 'type' => 'string', 'group' => 'general'],
            'default_trial_days' => ['value' => (string) $data['default_trial_days'], 'type' => 'int', 'group' => 'billing'],
            'allow_school_registration' => ['value' => $request->boolean('allow_school_registration') ? '1' : '0', 'type' => 'bool', 'group' => 'access'],
            'require_subscription_payment' => ['value' => $request->boolean('require_subscription_payment') ? '1' : '0', 'type' => 'bool', 'group' => 'billing'],
            'default_currency' => ['value' => $data['default_currency'], 'type' => 'string', 'group' => 'billing'],
            'maintenance_enabled' => ['value' => $request->boolean('maintenance_enabled') ? '1' : '0', 'type' => 'bool', 'group' => 'maintenance'],
            'maintenance_message' => ['value' => $data['maintenance_message'] ?? '', 'type' => 'string', 'group' => 'maintenance'],
        ];

        foreach ($rows as $key => $meta) {
            PlatformSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $meta['value'], 'type' => $meta['type'], 'group' => $meta['group']]
            );
        }

        PlatformSetting::forgetCache();

        $this->syncGlobalMaintenanceFlag($request->boolean('maintenance_enabled'), $request->user()->id, $data['maintenance_message'] ?? null);

        $this->activityLogger->log('settings_updated', 'Platform settings updated', ['keys' => array_keys($rows)]);

        return redirect()->route('platform.settings.index')->with('status', __('Settings saved.'));
    }

    private function syncGlobalMaintenanceFlag(bool $enabled, int $userId, ?string $message): void
    {
        $row = MaintenanceMode::query()->whereNull('tenant_id')->orderByDesc('id')->first();

        if ($row === null) {
            MaintenanceMode::query()->create([
                'tenant_id' => null,
                'is_enabled' => $enabled,
                'message' => $message,
                'starts_at' => $enabled ? now() : null,
                'ends_at' => null,
                'enabled_by' => $userId,
            ]);

            return;
        }

        $row->update([
            'is_enabled' => $enabled,
            'message' => $message,
            'enabled_by' => $userId,
            'starts_at' => $enabled ? ($row->starts_at ?? now()) : null,
        ]);
    }
}
