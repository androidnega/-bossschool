<?php

namespace App\Http\Controllers\Web;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\SuspiciousActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TenantAuditLogController extends Controller
{
    public function index(Request $request, SuspiciousActivityService $suspicious): View
    {
        $this->authorizeAdmin();
        $tenantId = (int) auth()->user()->tenant_id;

        $logs = $this->filteredQuery($request, $tenantId)
            ->orderByDesc('created_at')
            ->paginate(40)
            ->withQueryString();

        $actors = User::query()->where('tenant_id', $tenantId)->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
        $actions = ActivityLog::query()->where('tenant_id', $tenantId)->distinct()->orderBy('action')->pluck('action');

        $flags = $suspicious->findFor($tenantId, Carbon::now()->subDays(7));

        return view('audit_logs.index', [
            'logs' => $logs,
            'actors' => $actors,
            'actions' => $actions,
            'flags' => $flags,
            'filters' => $request->only(['search', 'action', 'actor_id', 'module', 'from', 'to']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeAdmin();
        $tenantId = (int) auth()->user()->tenant_id;

        $rows = $this->filteredQuery($request, $tenantId)
            ->orderByDesc('created_at')
            ->limit(10000)
            ->get();

        $filename = 'audit-log-tenant-'.$tenantId.'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id', 'created_at', 'action', 'actor_name', 'actor_role', 'description', 'target_type', 'target_id', 'ip_address']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->id,
                    $r->created_at?->format('Y-m-d H:i:s'),
                    $r->action,
                    $r->actor_name,
                    $r->actor_role,
                    $r->description,
                    $r->target_type,
                    $r->target_id,
                    $r->ip_address,
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function filteredQuery(Request $request, int $tenantId)
    {
        $q = ActivityLog::query()->where('tenant_id', $tenantId);

        if ($request->filled('search')) {
            $s = '%'.$request->string('search')->trim().'%';
            $q->where(function ($w) use ($s): void {
                $w->where('description', 'like', $s)
                    ->orWhere('action', 'like', $s)
                    ->orWhere('actor_name', 'like', $s);
            });
        }
        if ($request->filled('action')) {
            $q->where('action', $request->string('action'));
        }
        if ($request->filled('actor_id')) {
            $q->where('actor_id', (int) $request->query('actor_id'));
        }
        if ($request->filled('module')) {
            $q->where('action', 'like', $request->string('module').'%');
        }
        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->date('from')->toDateString());
        }
        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->date('to')->toDateString());
        }

        return $q;
    }

    private function authorizeAdmin(): void
    {
        $u = auth()->user();
        if (! $u || ! in_array($u->role, [UserRole::Admin->value, UserRole::Proprietor->value], true)) {
            abort(403);
        }
    }
}
