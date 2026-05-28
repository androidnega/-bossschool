<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\StorePlanRequest;
use App\Http\Requests\Platform\UpdatePlanRequest;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.manage');

        $plans = Plan::query()
            ->orderByRaw('sort_order IS NULL, sort_order')
            ->orderBy('name')
            ->get();

        return view('platform.plans.index', compact('plans'));
    }

    public function create(): View
    {
        $this->authorize('platform.manage');

        return view('platform.plans.create');
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $plan = Plan::query()->create([
            'name' => $data['name'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'max_students' => $data['max_students'],
            'max_staff' => $data['max_staff'],
            'features' => $data['features'] ?? [],
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $data['sort_order'] ?? null,
        ]);

        $this->activityLogger->log(
            'plan_created',
            'Created plan '.$plan->name,
            ['plan_id' => $plan->id, 'price' => (string) $plan->price],
            null,
            Plan::class,
            $plan->id
        );

        return redirect()->route('platform.plans.index')->with('status', __('Plan created.'));
    }

    public function edit(Plan $plan): View
    {
        $this->authorize('platform.manage');

        return view('platform.plans.edit', compact('plan'));
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $data = $request->validated();
        $plan->update([
            'name' => $data['name'],
            'price' => $data['price'],
            'billing_cycle' => $data['billing_cycle'],
            'max_students' => $data['max_students'],
            'max_staff' => $data['max_staff'],
            'features' => $data['features'] ?? [],
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? null,
        ]);

        $this->activityLogger->log(
            'plan_updated',
            'Updated plan '.$plan->name,
            ['plan_id' => $plan->id, 'price' => (string) $plan->price, 'is_active' => $plan->is_active],
            null,
            Plan::class,
            $plan->id
        );

        return redirect()->route('platform.plans.index')->with('status', __('Plan saved.'));
    }

    public function disable(Plan $plan): RedirectResponse
    {
        $this->authorize('platform.manage');
        $plan->update(['is_active' => false]);

        $this->activityLogger->log(
            'plan_disabled',
            'Disabled plan '.$plan->name,
            ['plan_id' => $plan->id],
            null,
            Plan::class,
            $plan->id
        );

        return back()->with('status', __('Plan disabled for new sales.'));
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        $this->authorize('platform.manage');

        $using = $plan->tenants()
            ->whereIn('status', [Tenant::STATUS_ACTIVE, Tenant::STATUS_TRIAL])
            ->count();

        if ($using > 0) {
            return back()->withErrors([
                'plan' => __('This plan is still assigned to active or trial schools. Disable it instead.'),
            ]);
        }

        $name = $plan->name;
        $id = $plan->id;
        $plan->delete();

        $this->activityLogger->log(
            'plan_deleted',
            'Deleted plan '.$name,
            ['plan_id' => $id],
            null,
            Plan::class,
            $id
        );

        return redirect()->route('platform.plans.index')->with('status', __('Plan deleted.'));
    }
}
