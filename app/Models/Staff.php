<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use App\Support\GhanaPhone;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends BaseModel
{
    use RecordsAuditTrail, SoftDeletes;

    protected $table = 'staff';

    protected $fillable = [
        'tenant_id',
        'name',
        'role',
        'subject',
        'phone',
        'salary',
    ];

    protected function casts(): array
    {
        return [
            'salary' => 'decimal:2',
        ];
    }

    /**
     * Normalise `phone` to its canonical +233XXXXXXXXX form when it looks
     * like a valid Ghana number.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => GhanaPhone::normalize($value),
        );
    }
}
