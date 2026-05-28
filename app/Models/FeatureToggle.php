<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureToggle extends Model
{
    public const SCOPE_GLOBAL = 'global';

    public const SCOPE_TENANT = 'tenant';

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_enabled',
        'scope',
        'tenant_id',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public static function isGloballyEnabled(string $key): bool
    {
        $row = self::query()->where('key', $key)->whereNull('tenant_id')->first();

        // Unseeded environments: do not block routes until an explicit row exists.
        return $row?->is_enabled ?? true;
    }
}
