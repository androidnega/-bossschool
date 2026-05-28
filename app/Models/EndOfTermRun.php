<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EndOfTermRun extends BaseModel
{
    use RecordsAuditTrail;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_REOPENED = 'reopened';

    public const STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_REVIEWED,
        self::STATUS_CLOSED,
        self::STATUS_REOPENED,
    ];

    /**
     * Default checklist keys. Stored as JSON so a school's run can carry the
     * boolean state per item without schema churn. UI iterates these labels.
     *
     * @var array<int, string>
     */
    public const DEFAULT_CHECKLIST = [
        'attendance_completed',
        'results_entered',
        'report_card_meta_completed',
        'report_cards_generated',
        'fee_balances_reviewed',
        'next_term_fees_entered',
        'promotion_reviewed',
        'data_exported',
    ];

    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'term_id',
        'initiated_by_user_id',
        'closed_by_user_id',
        'reopened_by_user_id',
        'status',
        'checklist',
        'closed_at',
        'reopened_at',
        'notes',
        'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'checklist' => 'array',
            'closed_at' => 'datetime',
            'reopened_at' => 'datetime',
        ];
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_user_id');
    }

    public function reopener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by_user_id');
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }
}
