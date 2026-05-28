<?php

namespace App\Jobs\Concerns;

use App\Models\Tenant;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Log;

/**
 * Mix-in for queued jobs that need an explicit tenant context.
 *
 * Pattern in the consuming job:
 *
 *   class FooJob implements ShouldQueue
 *   {
 *       use Dispatchable, Queueable, SerializesModels, RunsInTenantContext;
 *
 *       public function __construct(public int $tenantId) {}
 *
 *       public function handle(): void
 *       {
 *           $this->runForTenant(function (): void {
 *               // tenant-scoped models work normally here
 *           });
 *       }
 *   }
 *
 * The trait does three things:
 *   1. Forces every job to carry a tenant_id and refuse to run without one.
 *   2. Pushes that tenant onto the container before invoking the callback.
 *   3. Routes any failure through Log::error with tenant_id + job class.
 */
trait RunsInTenantContext
{
    /**
     * Subclasses must expose tenantId so the trait can find it.
     * If you store the tenant under a different name, override this method.
     */
    public function resolveTenantId(): ?int
    {
        if (property_exists($this, 'tenantId')) {
            return (int) $this->tenantId;
        }

        return null;
    }

    /**
     * Run $callback inside the resolved tenant's context. Returns whatever
     * the callback returns. Throws if the job has no tenant_id.
     */
    protected function runForTenant(\Closure $callback): mixed
    {
        $tenantId = $this->resolveTenantId();
        if ($tenantId === null) {
            $this->logFailure('Job dispatched without tenant_id', null);

            throw new \RuntimeException(sprintf(
                '%s requires a tenant_id but none was provided.',
                static::class
            ));
        }

        $tenant = Tenant::query()->find($tenantId);
        if ($tenant === null) {
            $this->logFailure('Tenant not found', $tenantId);

            throw new \RuntimeException(sprintf('Tenant %d not found.', $tenantId));
        }

        return Tenancy::run($tenant, $callback);
    }

    public function failed(?\Throwable $e = null): void
    {
        Log::error('Queued job failed', [
            'job' => static::class,
            'tenant_id' => $this->resolveTenantId(),
            'error' => $e?->getMessage(),
        ]);
    }

    private function logFailure(string $message, ?int $tenantId): void
    {
        Log::warning($message, [
            'job' => static::class,
            'tenant_id' => $tenantId,
        ]);
    }
}
