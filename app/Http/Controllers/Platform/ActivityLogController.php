<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('platform.manage');

        $q = ActivityLog::query()->with('tenant')->orderByDesc('created_at');

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

        if ($request->filled('tenant_id')) {
            $q->where('tenant_id', (int) $request->query('tenant_id'));
        }

        if ($request->filled('from')) {
            $q->whereDate('created_at', '>=', $request->date('from')->toDateString());
        }

        if ($request->filled('to')) {
            $q->whereDate('created_at', '<=', $request->date('to')->toDateString());
        }

        $logs = $q->paginate(40)->withQueryString();

        $tenants = Tenant::query()->orderBy('name')->get(['id', 'name']);
        $actors = User::withoutGlobalScopes()->orderBy('name')->limit(200)->get(['id', 'name', 'email']);
        $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

        return view('platform.activity-logs.index', [
            'logs' => $logs,
            'tenants' => $tenants,
            'actors' => $actors,
            'actions' => $actions,
            'filters' => $request->only(['search', 'action', 'actor_id', 'tenant_id', 'from', 'to']),
        ]);
    }
}
