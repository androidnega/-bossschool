<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Support\TenantResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->is('up')) {
            return $next($request);
        }

        $subdomain = TenantResolver::subdomainFromHost($request->getHost());
        $tenant = null;
        $resolvedBy = 'subdomain';

        if ($subdomain !== null && $subdomain !== '') {
            $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

            if ($tenant === null) {
                abort(404);
            }

            $user = $request->user();

            // Tenant users on a tenant host must belong to that tenant.
            // SuperAdmin (tenant_id null) is allowed but explicitly marked so we
            // can flag the access in UI/audit downstream.
            if ($user !== null && $user->tenant_id !== null && (int) $user->tenant_id !== (int) $tenant->id) {
                abort(403);
            }

            if ($user !== null && $user->tenant_id === null) {
                $resolvedBy = 'superadmin_on_tenant_host';
            }
        } else {
            // Bare hosts (localhost / IP) only get the "use user's tenant_id"
            // fallback in non-production environments to keep dev workflow easy.
            if (TenantResolver::isProductionLike()) {
                abort(404);
            }

            $user = $request->user();

            if ($user === null) {
                abort(404);
            }

            if ($user->tenant_id === null) {
                abort(403);
            }

            $tenant = Tenant::query()->find($user->tenant_id);

            if ($tenant === null) {
                abort(404);
            }

            $resolvedBy = 'user_tenant_id';
        }

        if ($tenant->status === Tenant::STATUS_SUSPENDED) {
            abort(403);
        }

        app()->instance('currentTenant', $tenant);
        $request->attributes->set('tenant', $tenant);
        $request->attributes->set('tenant_resolved_by', $resolvedBy);

        return $next($request);
    }
}
