<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionHistory extends BaseModel
{
    use RecordsAuditTrail;

    public const STATUS_PROMOTED = 'promoted';

    public const STATUS_REPEATED = 'repeated';

    public const STATUS_GRADUATED = 'graduated';

    public const STATUSES = [
        self::STATUS_PROMOTED,
        self::STATUS_REPEATED,
        self::STATUS_GRADUATED,
    ];

    protected $table = 'promotion_history';

    protected $fillable = [
        'tenant_id',
        'student_id',
        'from_class_id',
        'to_class_id',
        'from_academic_year_id',
        'to_academic_year_id',
        'promoted_by_user_id',
        'status',
        'notes',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'from_class_id');
    }

    public function toClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'to_class_id');
    }

    public function fromAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'from_academic_year_id');
    }

    public function toAcademicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'to_academic_year_id');
    }

    public function promoter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by_user_id');
    }
}
