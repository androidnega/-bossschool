<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateLevel extends Model
{
    use HasFactory;

    public const CODE_KG            = 'KG';
    public const CODE_LOWER_PRIMARY = 'LOWER_PRIMARY';
    public const CODE_UPPER_PRIMARY = 'UPPER_PRIMARY';
    public const CODE_JHS           = 'JHS';

    protected $fillable = [
        'school_template_id',
        'name',
        'code',
        'sort_order',
        'is_optional',
    ];

    protected function casts(): array
    {
        return [
            'is_optional' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SchoolTemplate::class, 'school_template_id');
    }

    public function classes(): HasMany
    {
        return $this->hasMany(TemplateClass::class)->orderBy('sort_order');
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(TemplateSubject::class)->orderBy('sort_order');
    }
}
