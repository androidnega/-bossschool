<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use App\Services\OnboardingWizardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Pilot onboarding wizard for Proprietor/Admin. The wizard is informational
 * — clicking "Mark step done" merely records progress; the underlying data
 * has to actually exist (auto-detection) before the tenant can finish.
 */
class OnboardingWizardController extends Controller
{
    public function __construct(
        private readonly OnboardingWizardService $wizard,
        private readonly ActivityLogger $logger,
    ) {}

    public function index(Request $request): View|RedirectResponse
    {
        $tenant = $this->guardedTenant($request);
        $progress = $this->wizard->progressFor($tenant);
        $statuses = $this->wizard->stepStatuses($tenant);

        return view('onboarding.wizard', [
            'tenant' => $tenant,
            'steps' => $statuses,
            'progress' => $progress,
            'current_step' => (int) $progress->current_step,
            'can_finish' => $this->wizard->canFinish($tenant),
            'finished' => (bool) $tenant->onboarding_complete,
        ]);
    }

    public function show(Request $request, int $step): View|RedirectResponse
    {
        $tenant = $this->guardedTenant($request);
        $progress = $this->wizard->progressFor($tenant);
        $progress->forceFill(['current_step' => $step, 'updated_by_user_id' => $request->user()->id])->save();

        $meta = OnboardingWizardService::STEPS[$step] ?? null;
        if ($meta === null) {
            return redirect()->route('onboarding.wizard.index');
        }

        return view('onboarding.wizard_step', [
            'tenant' => $tenant,
            'step' => $step,
            'meta' => $meta,
            'auto' => $this->wizard->autoDetected($tenant),
            'statuses' => $this->wizard->stepStatuses($tenant),
            'progress' => $progress,
        ]);
    }

    public function markStep(Request $request, int $step): RedirectResponse
    {
        $tenant = $this->guardedTenant($request);
        $meta = OnboardingWizardService::STEPS[$step] ?? null;
        if ($meta === null) {
            throw ValidationException::withMessages(['step' => __('Unknown wizard step.')]);
        }
        $progress = $this->wizard->progressFor($tenant);
        $progress->markStepDone($meta['key'])->forceFill([
            'current_step' => min($step + 1, count(OnboardingWizardService::STEPS)),
            'updated_by_user_id' => $request->user()->id,
        ])->save();

        $this->logger->log(
            'onboarding_step_marked',
            'Onboarding step marked done',
            ['step' => $step, 'key' => $meta['key']],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return redirect()->route('onboarding.wizard.show', ['step' => min($step + 1, count(OnboardingWizardService::STEPS))]);
    }

    public function finish(Request $request): RedirectResponse
    {
        $tenant = $this->guardedTenant($request);
        if (! $this->wizard->finish($tenant, (int) $request->user()->id)) {
            throw ValidationException::withMessages([
                'wizard' => __('All essential steps (1-9) must be complete before finishing onboarding.'),
            ]);
        }
        $this->logger->log(
            'onboarding_finished',
            'Tenant onboarding marked complete',
            ['tenant_id' => $tenant->id],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return redirect()->route('onboarding.wizard.index')->with('status', __('Onboarding marked complete.'));
    }

    private function guardedTenant(Request $request): Tenant
    {
        $user = $request->user();
        abort_unless(in_array((string) $user->role, ['Proprietor', 'Admin'], true), 403);
        $tenant = app('currentTenant');
        if (! $tenant) {
            $tenant = Tenant::query()->findOrFail((int) $user->tenant_id);
        }

        return $tenant;
    }
}
