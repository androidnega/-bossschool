<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends BaseModel
{
    use RecordsAuditTrail;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'name',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }
}
