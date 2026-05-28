<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportCardMeta extends BaseModel
{
    use RecordsAuditTrail;

    protected $table = 'report_card_meta';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'academic_year_id',
        'term_id',
        'days_school_opened',
        'days_present',
        'days_absent',
        'position_in_class',
        'class_size',
        'conduct',
        'attitude',
        'interest',
        'class_teacher_remark',
        'head_teacher_remark',
        'next_term_fee',
        'vacation_date',
        'reopening_date',
        'class_teacher_signature',
        'head_teacher_signature',
    ];

    protected function casts(): array
    {
        return [
            'vacation_date' => 'date',
            'reopening_date' => 'date',
            'next_term_fee' => 'decimal:2',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }
}
