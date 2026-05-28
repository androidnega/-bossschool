<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Default fee-item placeholder for a template. `amount` is intentionally
 * nullable — each school sets its own amounts after onboarding.
 */
class TemplateFeeItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_template_id',
        'name',
        'description',
        'amount',
        'is_optional',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_optional' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SchoolTemplate::class, 'school_template_id');
    }
}
