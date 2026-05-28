<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    public const BILLING_MONTHLY = 'monthly';

    public const BILLING_YEARLY = 'yearly';

    protected $fillable = [
        'name',
        'price',
        'billing_cycle',
        'features',
        'is_active',
        'sort_order',
        'max_students',
        'max_staff',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'max_students' => 'integer',
            'max_staff' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return HasMany<Tenant, Plan>
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    public function activeTenantsCount(): int
    {
        return $this->tenants()
            ->where('status', '!=', Tenant::STATUS_SUSPENDED)
            ->count();
    }
}
