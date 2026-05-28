<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Http\Request;

/**
 * Centralised, strict tenant resolution from the request host.
 *
 * - In production, only a real tenant subdomain resolves to a tenant.
 *   Bare hosts (localhost / IPs / single-label hosts) never fall back to
 *   "the authenticated user's tenant" because that path is how cross-tenant
 *   identity bugs hide.
 * - In local / testing environments, bare hosts are allowed to resolve via
 *   the authenticated user's tenant_id (single-developer workflow).
 */
final class TenantResolver
{
    public static function subdomainFromHost(string $host): ?string
    {
        if ($host === '' || $host === 'localhost' || $host === '127.0.0.1' || filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        $hostNoPort = explode(':', $host)[0];
        $parts = explode('.', $hostNoPort);

        if (count($parts) === 2 && $parts[1] === 'localhost') {
            return $parts[0] === 'www' ? null : $parts[0];
        }

        if (count($parts) >= 3) {
            $first = $parts[0];

            return $first === 'www' ? $parts[1] : $first;
        }

        return null;
    }

    public static function resolveFromRequest(Request $request): ?Tenant
    {
        $subdomain = self::subdomainFromHost($request->getHost());

        if ($subdomain === null || $subdomain === '') {
            return null;
        }

        return Tenant::query()->where('subdomain', $subdomain)->first();
    }

    public static function isProductionLike(): bool
    {
        return ! in_array(app()->environment(), ['local', 'testing'], true);
    }
}
