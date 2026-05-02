<?php

namespace App\Models;

class Payment extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'student_id',
        'amount',
        'method',
        'reference',
        'date',
        'receipt_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }
}
