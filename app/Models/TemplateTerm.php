<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateTerm extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_template_id',
        'name',
        'short_name',
        'sort_order',
        'is_active_default',
    ];

    protected function casts(): array
    {
        return [
            'is_active_default' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SchoolTemplate::class, 'school_template_id');
    }
}
