<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use App\Support\GhanaPhone;
use Illuminate\Database\Eloquent\Casts\Attribute;

class School extends BaseModel
{
    use RecordsAuditTrail;

    protected $fillable = [
        'tenant_id',
        'name',
        'logo',
        'address',
        'phone',
        'email',
        'academic_year',
        'ges_region',
        'ges_district',
        'ges_circuit',
        'school_code',
        'head_teacher_name',
        'motto',
    ];

    /**
     * Normalise the school's contact `phone` to canonical +233XXXXXXXXX form.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => GhanaPhone::normalize($value),
        );
    }
}
