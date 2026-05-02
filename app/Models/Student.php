<?php

namespace App\Models;

use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Student extends BaseModel
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'name',
        'photo',
        'gender',
        'dob',
        'parent_name',
        'parent_phone',
        'address',
        'admission_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'dob' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
