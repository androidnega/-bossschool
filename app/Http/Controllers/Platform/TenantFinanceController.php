<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Payment;
use App\Models\Tenant;
use App\Services\Platform\TenantMetricsService;
use Carbon\Carbon;
use Illuminate\View\View;

class TenantFinanceController extends Controller
{
    public function __construct(
        private TenantMetricsService $metrics
    ) {}

    public function index(Tenant $tenant): View
    {
        $this->authorize('platform.viewTenantFinance');

        $tid = (int) $tenant->id;
        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::today()->startOfMonth()->toDateString();

        $paymentsTotal = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tid)->sum('amount');
        $paymentsToday = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tid)->where('date', $today)->sum('amount');
        $paymentsMonth = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tid)->where('date', '>=', $monthStart)->sum('amount');

        $expectedFees = (float) Fee::withoutGlobalScopes()->where('tenant_id', $tid)->sum('amount');
        $paidTotal = (float) Payment::withoutGlobalScopes()->where('tenant_id', $tid)->sum('amount');
        $outstanding = max(0.0, $expectedFees - $paidTotal);

        $debtors = $this->metrics->debtorsSummary($tid);

        $recentPayments = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->with('student')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $methods = Payment::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->selectRaw('payment_channel as method, COUNT(*) as c, SUM(amount) as total')
            ->groupBy('payment_channel')
            ->get();

        $feeTypes = Fee::withoutGlobalScopes()
            ->where('tenant_id', $tid)
            ->selectRaw('fee_type, COUNT(*) as c, SUM(amount) as total')
            ->groupBy('fee_type')
            ->orderBy('fee_type')
            ->get();

        return view('platform.tenant-finance.index', compact(
            'tenant',
            'paymentsTotal',
            'paymentsToday',
            'paymentsMonth',
            'outstanding',
            'debtors',
            'recentPayments',
            'methods',
            'feeTypes'
        ));
    }
}
