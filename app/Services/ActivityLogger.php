<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ActivityLogger
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function log(
        string $action,
        ?string $description = null,
        ?array $metadata = null,
        ?int $tenantId = null,
        ?string $targetType = null,
        ?int $targetId = null,
    ): void {
        $user = Auth::user();

        ActivityLog::query()->create([
            'actor_id' => $user?->id,
            'actor_name' => $user?->name,
            'actor_role' => $user?->role,
            'tenant_id' => $tenantId ?? $user?->tenant_id,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'action' => $action,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()?->ip(),
            'user_agent' => Str::limit((string) request()?->userAgent(), 512),
        ]);
    }
}
