<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Result extends BaseModel
{
    use RecordsAuditTrail, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'term_id',
        'student_id',
        'subject_id',
        'class_test',
        'midterm',
        'exam',
        'total',
        'grade',
    ];

    protected function casts(): array
    {
        return [
            'class_test' => 'decimal:2',
            'midterm' => 'decimal:2',
            'exam' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Result $result): void {
            $ct = (float) ($result->class_test ?? 0);
            $mid = (float) ($result->midterm ?? 0);
            $ex = (float) ($result->exam ?? 0);
            $total = round($ct + $mid + $ex, 2);
            $result->total = $total;
            $result->grade = self::letterGradeFromTotal($total);
        });
    }

    public static function letterGradeFromTotal(float $total): string
    {
        if ($total >= 80.0) {
            return 'A';
        }
        if ($total >= 70.0) {
            return 'B';
        }
        if ($total >= 60.0) {
            return 'C';
        }
        if ($total >= 50.0) {
            return 'D';
        }

        return 'F';
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
