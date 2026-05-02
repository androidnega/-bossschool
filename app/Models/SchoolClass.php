<?php

namespace App\Models;

class SchoolClass extends BaseModel
{
    protected $table = 'classes';

    protected $fillable = [
        'tenant_id',
        'name',
        'section',
    ];
}
