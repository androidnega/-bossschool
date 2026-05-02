<?php

namespace App\Models;

class School extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'name',
        'logo',
        'address',
        'phone',
        'email',
        'academic_year',
    ];
}
