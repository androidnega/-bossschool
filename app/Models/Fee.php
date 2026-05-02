<?php

namespace App\Models;

class Fee extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'class_id',
        'term_id',
        'fee_type',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
