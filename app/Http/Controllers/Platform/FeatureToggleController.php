<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\UpdateFeatureTogglesRequest;
use App\Models\FeatureToggle;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeatureToggleController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.manage');

        $byKey = FeatureToggle::query()->whereNull('tenant_id')->get()->keyBy('key');
        $toggles = collect(UpdateFeatureTogglesRequest::GLOBAL_KEYS)
            ->map(function (string $key) use ($byKey): FeatureToggle {
                return $byKey->get($key) ?? new FeatureToggle([
                    'key' => $key,
                    'name' => ucfirst(str_replace('_', ' ', $key)),
                    'is_enabled' => false,
                    'scope' => FeatureToggle::SCOPE_GLOBAL,
                ]);
            })
            ->values();

        return view('platform.feature-toggles.index', compact('toggles'));
    }

    public function update(UpdateFeatureTogglesRequest $request): RedirectResponse
    {
        $payload = $request->validated('toggles', []);

        foreach ($payload as $key => $enabled) {
            FeatureToggle::query()->updateOrCreate(
                ['key' => $key, 'tenant_id' => null],
                [
                    'name' => ucfirst(str_replace('_', ' ', $key)),
                    'is_enabled' => (bool) $enabled,
                    'scope' => FeatureToggle::SCOPE_GLOBAL,
                ]
            );
        }

        $this->activityLogger->log(
            'feature_toggle_changed',
            'Global feature toggles updated',
            ['keys' => array_keys($payload)]
        );

        return redirect()->route('platform.feature-toggles.index')->with('status', __('Feature toggles saved.'));
    }
}
