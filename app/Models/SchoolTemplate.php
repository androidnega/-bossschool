<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A blueprint for setting up a new school's classes / subjects / terms / fees.
 * Platform-wide (no tenant_id). Cloned into real tables on tenant provisioning
 * by App\Services\SchoolTemplateApplyService.
 */
class SchoolTemplate extends Model
{
    use HasFactory;

    public const CODE_PRIMARY_ONLY  = 'GH_PRIMARY_ONLY';
    public const CODE_JHS_ONLY      = 'GH_JHS_ONLY';
    public const CODE_PRIMARY_JHS   = 'GH_PRIMARY_JHS';
    public const CODE_FULL_BASIC    = 'GH_FULL_BASIC';

    protected $fillable = [
        'name',
        'code',
        'description',
        'country',
        'curriculum_label',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function levels(): HasMany
    {
        return $this->hasMany(TemplateLevel::class)->orderBy('sort_order');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(TemplateClass::class)->orderBy('sort_order');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(TemplateSubject::class)->orderBy('sort_order');
    }

    public function terms(): HasMany
    {
        return $this->hasMany(TemplateTerm::class)->orderBy('sort_order');
    }

    public function feeItems(): HasMany
    {
        return $this->hasMany(TemplateFeeItem::class)->orderBy('sort_order');
    }
}
