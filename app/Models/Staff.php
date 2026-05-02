<?php

namespace App\Models;

class Staff extends BaseModel
{
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
}
