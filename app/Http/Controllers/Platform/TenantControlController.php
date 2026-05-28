<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceMode;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\Platform\TenantMetricsService;
use Illuminate\View\View;

class TenantControlController extends Controller
{
    public function __construct(
        private TenantMetricsService $metrics
    ) {}

    public function show(Tenant $tenant): View
    {
        $this->authorize('platform.manageTenants');

        $tenant->load([
            'plan',
            'school',
            'subscriptions' => fn ($q) => $q->withoutGlobalScopes()->orderByDesc('start_date')->limit(3),
        ]);

        $tid = (int) $tenant->id;
        $summary = $this->metrics->controlCenterSummary($tid);
        $debtors = $this->metrics->debtorsSummary($tid);

        $activeSubscription = $tenant->subscriptions->firstWhere('status', Subscription::STATUS_ACTIVE)
            ?? $tenant->subscriptions->first();

        $tenantMaintenance = MaintenanceMode::query()
            ->where('tenant_id', $tenant->id)
            ->orderByDesc('id')
            ->first();

        return view('platform.tenants.control', [
            'tenant' => $tenant,
            'summary' => $summary,
            'debtors' => $debtors,
            'activeSubscription' => $activeSubscription,
            'tenantMaintenance' => $tenantMaintenance,
        ]);
    }
}
