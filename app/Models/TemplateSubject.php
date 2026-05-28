<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A subject blueprint. May be scoped to a level (e.g. all JHS classes) OR
 * to a specific class. If both are null, the subject applies to every class
 * in the template.
 */
class TemplateSubject extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_template_id',
        'template_level_id',
        'template_class_id',
        'name',
        'short_name',
        'code',
        'is_core',
        'is_editable',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_editable' => 'boolean',
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

    public function templateClass(): BelongsTo
    {
        return $this->belongsTo(TemplateClass::class, 'template_class_id');
    }
}
