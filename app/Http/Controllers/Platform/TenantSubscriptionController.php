<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\ExtendTenantSubscriptionRequest;
use App\Http\Requests\Platform\UpdateTenantSubscriptionRequest;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TenantSubscriptionController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.manageTenantSubscription');

        $tenant->load(['plan']);
        $tenant->setRelation(
            'subscriptions',
            Subscription::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->with('plan')
                ->orderByDesc('start_date')
                ->limit(20)
                ->get()
        );
        $plans = Plan::query()->orderByRaw('sort_order IS NULL, sort_order')->orderBy('name')->get();

        $current = $tenant->subscriptions->firstWhere('status', Subscription::STATUS_ACTIVE)
            ?? $tenant->subscriptions->first();

        return view('platform.tenant-subscription.index', compact('tenant', 'plans', 'current'));
    }

    public function update(UpdateTenantSubscriptionRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenantSubscription');

        $data = $request->validated();
        $plan = Plan::query()->findOrFail((int) $data['plan_id']);

        Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->update(['status' => Subscription::STATUS_EXPIRED]);

        $amount = $data['amount'] ?? $plan->price;
        $billingCycle = $data['billing_cycle'] ?? $plan->billing_cycle;

        Subscription::query()->withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'plan_id' => (int) $data['plan_id'],
            'amount' => $amount,
            'billing_cycle' => $billingCycle,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'] ?? null,
            'status' => $data['status'],
            'note' => $data['note'] ?? null,
            'changed_by' => $request->user()->id,
            'payment_id' => null,
        ]);

        $tenant->update(['plan_id' => (int) $data['plan_id']]);

        $this->activityLogger->log(
            'subscription_changed',
            'Tenant subscription updated',
            [
                'plan_id' => (int) $data['plan_id'],
                'status' => $data['status'],
                'amount' => (string) $amount,
            ],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return redirect()
            ->route('platform.tenants.subscription.index', $tenant)
            ->with('status', __('Subscription updated.'));
    }

    public function extend(ExtendTenantSubscriptionRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenantSubscription');

        $days = (int) $request->validated('days');
        $sub = Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->first();

        if ($sub === null) {
            return back()->withErrors(['extend' => __('No active subscription to extend.')]);
        }

        $base = $sub->end_date ?? now();
        $sub->end_date = $base->copy()->addDays($days);
        $sub->changed_by = $request->user()->id;
        $sub->save();

        $this->activityLogger->log(
            'subscription_changed',
            'Subscription extended',
            ['days' => $days, 'subscription_id' => $sub->id],
            $tenant->id,
            Subscription::class,
            $sub->id
        );

        return back()->with('status', __('Subscription extended.'));
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenantSubscription');

        $sub = Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', Subscription::STATUS_ACTIVE)
            ->first();

        if ($sub !== null) {
            $sub->update([
                'status' => Subscription::STATUS_CANCELLED,
                'changed_by' => request()->user()?->id,
            ]);
        }

        $this->activityLogger->log(
            'subscription_changed',
            'Subscription suspended (cancelled)',
            ['subscription_id' => $sub?->id],
            $tenant->id,
            Tenant::class,
            $tenant->id
        );

        return back()->with('status', __('Subscription suspended.'));
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('platform.manageTenantSubscription');

        $sub = Subscription::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('start_date')
            ->first();

        if ($sub === null) {
            return back()->withErrors(['activate' => __('No subscription row to activate.')]);
        }

        $sub->update([
            'status' => Subscription::STATUS_ACTIVE,
            'changed_by' => request()->user()?->id,
        ]);

        $this->activityLogger->log(
            'subscription_changed',
            'Subscription activated',
            ['subscription_id' => $sub->id],
            $tenant->id,
            Subscription::class,
            $sub->id
        );

        return back()->with('status', __('Subscription activated.'));
    }
}
