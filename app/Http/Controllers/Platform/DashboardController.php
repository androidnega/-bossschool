<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $this->authorize('platform.manage');

        $tenantQuery = Tenant::query();

        $recentTenants = (clone $tenantQuery)->orderByDesc('created_at')->limit(8)->get();

        $activeSubscriptions = Subscription::query()->withoutGlobalScopes()->where('status', Subscription::STATUS_ACTIVE)->count();
        $expiredSubscriptions = Subscription::query()->withoutGlobalScopes()->where('status', Subscription::STATUS_EXPIRED)->count();

        $recentSubscriptionChanges = Subscription::query()->withoutGlobalScopes()
            ->with(['tenant', 'plan'])
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get();

        $trialSoon = (clone $tenantQuery)
            ->where('status', Tenant::STATUS_TRIAL)
            ->whereNotNull('trial_end')
            ->where('trial_end', '<=', now()->addDays(7))
            ->orderBy('trial_end')
            ->limit(6)
            ->get();

        $subEndingSoon = Subscription::query()->withoutGlobalScopes()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<=', now()->addDays(14))
            ->whereDate('end_date', '>=', now()->toDateString())
            ->pluck('tenant_id')
            ->unique()
            ->filter()
            ->values();

        $tenantsNeedingAttention = Tenant::query()
            ->with('plan')
            ->where(function ($q) use ($subEndingSoon, $trialSoon): void {
                $q->where('status', Tenant::STATUS_SUSPENDED);
                if ($subEndingSoon->isNotEmpty()) {
                    $q->orWhereIn('id', $subEndingSoon->all());
                }
                if ($trialSoon->isNotEmpty()) {
                    $q->orWhereIn('id', $trialSoon->pluck('id')->all());
                }
            })
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        return view('platform.dashboard', [
            'tenantCount' => (clone $tenantQuery)->count(),
            'activeSchools' => (clone $tenantQuery)->where('status', Tenant::STATUS_ACTIVE)->count(),
            'suspendedSchools' => (clone $tenantQuery)->where('status', Tenant::STATUS_SUSPENDED)->count(),
            'trialSchools' => (clone $tenantQuery)->where('status', Tenant::STATUS_TRIAL)->count(),
            'activeSubscriptions' => $activeSubscriptions,
            'expiredSubscriptions' => $expiredSubscriptions,
            'recentTenants' => $recentTenants,
            'recentSubscriptionChanges' => $recentSubscriptionChanges,
            'tenantsNeedingAttention' => $tenantsNeedingAttention,
        ]);
    }
}
