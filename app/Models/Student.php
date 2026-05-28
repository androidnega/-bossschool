<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use App\Support\GhanaPhone;
use Database\Factories\StudentFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends BaseModel
{
    /** @use HasFactory<StudentFactory> */
    use HasFactory, RecordsAuditTrail, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'class_id',
        'name',
        'admission_no',
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

    /**
     * Normalise `parent_phone` to its canonical +233XXXXXXXXX form when it
     * looks like a valid Ghana number; leave junk values alone so validation
     * can still reject them upstream.
     */
    protected function parentPhone(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => GhanaPhone::normalize($value),
        );
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    /**
     * @return BelongsToMany<User, Student>
     */
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'user_id')
            ->withPivot('tenant_id')
            ->withTimestamps();
    }

    public function linkedUser(): HasOne
    {
        return $this->hasOne(User::class, 'student_id');
    }
}
