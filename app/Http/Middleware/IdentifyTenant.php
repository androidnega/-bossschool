<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
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

        $subdomain = $this->resolveSubdomain($request);

        if ($subdomain === null || $subdomain === '') {
            abort(404);
        }

        $tenant = Tenant::query()->where('subdomain', $subdomain)->first();

        if ($tenant === null) {
            abort(404);
        }

        if ($tenant->status === Tenant::STATUS_SUSPENDED) {
            abort(403);
        }

        app()->instance('currentTenant', $tenant);
        $request->attributes->set('tenant', $tenant);

        return $next($request);
    }

    private function resolveSubdomain(Request $request): ?string
    {
        $host = $request->getHost();

        if ($host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $parts = explode('.', $host);

        if (count($parts) === 2 && $parts[1] === 'localhost') {
            return $parts[0] === 'www' ? null : $parts[0];
        }

        if (count($parts) >= 3) {
            $first = $parts[0];

            return $first === 'www' ? $parts[1] : $first;
        }

        return null;
    }
}
