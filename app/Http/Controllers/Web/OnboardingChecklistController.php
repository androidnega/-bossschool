<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\OnboardingChecklistService;
use App\Services\PlanLimits;
use Illuminate\View\View;

class OnboardingChecklistController extends Controller
{
    public function index(OnboardingChecklistService $service, PlanLimits $limits): View
    {
        $user = auth()->user();
        if (! in_array($user->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            abort(403);
        }

        $tenant = Tenant::query()->findOrFail((int) $user->tenant_id);

        return view('onboarding.index', [
            'checklist' => $service->forTenant($tenant),
            'planLimits' => $limits->summary($tenant),
        ]);
    }
}
