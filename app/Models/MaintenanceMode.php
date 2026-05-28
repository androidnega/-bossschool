<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceMode extends Model
{
    protected $fillable = [
        'tenant_id',
        'is_enabled',
        'message',
        'starts_at',
        'ends_at',
        'enabled_by',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function enabledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enabled_by');
    }

    public static function globalRow(): ?self
    {
        return self::query()->whereNull('tenant_id')->orderByDesc('id')->first();
    }

    public static function isGlobalEnabled(): bool
    {
        $row = self::globalRow();

        return $row !== null && $row->is_enabled;
    }

    public static function isTenantEnabled(int $tenantId): bool
    {
        return self::query()
            ->where('tenant_id', $tenantId)
            ->where('is_enabled', true)
            ->exists();
    }
}
