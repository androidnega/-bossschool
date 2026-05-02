<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'price',
        'features',
        'max_students',
        'max_staff',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'price' => 'decimal:2',
            'max_students' => 'integer',
            'max_staff' => 'integer',
        ];
    }
}
