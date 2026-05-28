<?php

namespace App\Support;

use App\Models\Tenant;
use Closure;

/**
 * Tenancy::run($tenant, fn () => ...) is the explicit way to push a tenant
 * context onto the container for queued jobs, Artisan commands and tests.
 *
 * Web requests get the same `currentTenant` binding via the IdentifyTenant
 * middleware, so anything that runs in HTTP context already has the tenant.
 * Anything that runs OUTSIDE a request — a job, a scheduler tick, a command —
 * MUST wrap the work in Tenancy::run() so that global scopes
 * (e.g. BelongsToTenant) and audit logging pick up the correct tenant_id.
 *
 * The previous binding is restored on the way out, so this is safe to nest.
 */
class Tenancy
{
    /**
     * Resolve the currently bound tenant, if any. Returns null when running
     * outside any tenant context (platform commands, etc.).
     */
    public static function current(): ?Tenant
    {
        if (! app()->bound('currentTenant')) {
            return null;
        }

        $value = app('currentTenant');

        return $value instanceof Tenant ? $value : null;
    }

    /**
     * Push a tenant context, run the callback, and restore the previous
     * binding (or remove it if there wasn't one). The callback's return
     * value is forwarded so it can be used inline:
     *
     *   $count = Tenancy::run($tenant, fn () => Student::query()->count());
     */
    public static function run(Tenant|int $tenant, Closure $callback): mixed
    {
        $resolved = $tenant instanceof Tenant ? $tenant : Tenant::query()->findOrFail($tenant);

        $previous = app()->bound('currentTenant') ? app('currentTenant') : null;
        app()->instance('currentTenant', $resolved);

        try {
            return $callback($resolved);
        } finally {
            if ($previous === null) {
                app()->forgetInstance('currentTenant');
            } else {
                app()->instance('currentTenant', $previous);
            }
        }
    }

    /**
     * Run the callback for every active tenant. Errors in one tenant do not
     * stop the others; failures are returned so callers can decide what to do.
     *
     * @return array<int, array{tenant_id:int, ok:bool, result:mixed, error:?string}>
     */
    public static function eachTenant(Closure $callback): array
    {
        $tenants = Tenant::query()->whereNull('deleted_at')->orderBy('id')->get();
        $out = [];

        foreach ($tenants as $tenant) {
            try {
                $result = self::run($tenant, $callback);
                $out[] = ['tenant_id' => (int) $tenant->id, 'ok' => true, 'result' => $result, 'error' => null];
            } catch (\Throwable $e) {
                $out[] = ['tenant_id' => (int) $tenant->id, 'ok' => false, 'result' => null, 'error' => $e->getMessage()];
            }
        }

        return $out;
    }
}
