<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantBackup;
use App\Services\ActivityLogger;
use App\Services\TenantOperationalResetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResetController extends Controller
{
    public function __construct(
        private readonly TenantOperationalResetService $resetService,
        private readonly ActivityLogger $activityLogger
    ) {}

    public function index(): View
    {
        $this->authorize('platform.resetTenantData');

        $tenants = Tenant::query()->orderBy('name')->get();

        // Last 10 reset events, drawn from activity_logs so the feed survives
        // a page reload and is auditable.
        $recentResets = \App\Models\ActivityLog::query()
            ->whereIn('action', ['tenant_data_reset', 'system_data_reset'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('platform.reset.index', [
            'tenants' => $tenants,
            'allowResetAll' => (bool) config('platform.allow_reset_all', false),
            'recentResets' => $recentResets,
        ]);
    }

    public function resetTenant(Request $request): RedirectResponse
    {
        $this->authorize('platform.resetTenantData');

        $data = $request->validate([
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'confirm' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $tenant = Tenant::query()->findOrFail((int) $data['tenant_id']);

        $this->ensurePasswordReauth($request, $data['password']);

        $expected = 'RESET '.strtoupper($tenant->subdomain);
        if ($data['confirm'] !== $expected) {
            throw ValidationException::withMessages([
                'confirm' => __('Type :phrase exactly to confirm.', ['phrase' => $expected]),
            ]);
        }

        $this->ensureRecentBackup($tenant->id);

        $summary = $this->resetService->purgeTenantOperationalData($tenant->id);

        $this->activityLogger->log(
            'tenant_data_reset',
            sprintf('Operational data reset for tenant "%s"', $tenant->name),
            [
                'tenant_id' => $tenant->id,
                'subdomain' => $tenant->subdomain,
                'snapshot_path' => $summary['snapshot_path'],
                'counts' => $summary['counts'],
                'total_rows' => array_sum($summary['counts']),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ],
            $tenant->id
        );

        $totalRows = array_sum($summary['counts']);

        return redirect()->route('platform.reset.index')
            ->with('status', __('Reset complete. :n rows removed for :school.', [
                'n' => number_format($totalRows),
                'school' => $tenant->name,
            ]))
            ->with('reset_summary', [
                'scope' => 'single',
                'tenant_id' => (int) $tenant->id,
                'tenant_name' => (string) $tenant->name,
                'tenant_subdomain' => (string) $tenant->subdomain,
                'counts' => $summary['counts'],
                'total_rows' => $totalRows,
                'snapshot_path' => $summary['snapshot_path'],
                'at' => now()->toIso8601String(),
            ]);
    }

    public function resetAll(Request $request): RedirectResponse
    {
        $this->authorize('platform.resetTenantData');

        if (! config('platform.allow_reset_all', false)) {
            abort(403, __('The "reset all schools" action is disabled in this environment.'));
        }

        $data = $request->validate([
            'confirm' => ['required', 'string', 'in:RESET ALL TENANTS'],
            'password' => ['required', 'string'],
        ]);

        $this->ensurePasswordReauth($request, $data['password']);

        $summaries = $this->resetService->purgeAllTenantsOperationalData();

        $totals = array_reduce($summaries, function (array $carry, array $s): array {
            foreach ($s['counts'] as $table => $count) {
                $carry[$table] = ($carry[$table] ?? 0) + $count;
            }

            return $carry;
        }, []);

        $this->activityLogger->log(
            'system_data_reset',
            'All tenant operational data was reset (platform-wide)',
            [
                'tenants_affected' => count($summaries),
                'totals' => $totals,
                'snapshots' => array_map(fn ($s) => $s['snapshot_path'], $summaries),
                'ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]
        );

        return redirect()->route('platform.reset.index')
            ->with('status', __('Reset complete. :n rows removed across :count schools.', [
                'n' => number_format(array_sum($totals)),
                'count' => count($summaries),
            ]))
            ->with('reset_summary', [
                'scope' => 'all',
                'tenants_affected' => count($summaries),
                'counts' => $totals,
                'total_rows' => array_sum($totals),
                'snapshots' => array_map(fn ($s) => $s['snapshot_path'], $summaries),
                'at' => now()->toIso8601String(),
            ]);
    }

    /**
     * Force the SuperAdmin to re-authenticate by re-entering their password
     * before any destructive operation goes through.
     */
    private function ensurePasswordReauth(Request $request, string $password): void
    {
        $user = $request->user();
        if ($user === null || ! Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('The password you entered is incorrect.'),
            ]);
        }
    }

    /**
     * A destructive reset must be preceded by a recent completed backup so
     * the tenant has a manual restore option. Toggle off with
     * BACKUP_REQUIRED_FOR_RESET=false (not recommended in production).
     */
    private function ensureRecentBackup(int $tenantId): void
    {
        if (! config('backups.reset_requires_backup', true)) {
            return;
        }

        $maxAge = (int) config('backups.reset_backup_max_age_seconds', 60 * 60 * 24);
        $cutoff = now()->subSeconds($maxAge);

        $exists = TenantBackup::query()
            ->where('tenant_id', $tenantId)
            ->where('status', TenantBackup::STATUS_COMPLETED)
            ->where('created_at', '>=', $cutoff)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'confirm' => __('No recent backup found for this school. Take a backup first.'),
            ]);
        }
    }
}
