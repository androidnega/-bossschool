<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemplateClass extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_template_id',
        'template_level_id',
        'name',
        'short_name',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SchoolTemplate::class, 'school_template_id');
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(TemplateLevel::class, 'template_level_id');
    }
}
