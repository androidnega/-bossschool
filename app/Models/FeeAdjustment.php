<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeeAdjustment extends BaseModel
{
    use RecordsAuditTrail, SoftDeletes;

    public const TYPE_DISCOUNT = 'discount';

    public const TYPE_SCHOLARSHIP = 'scholarship';

    public const TYPE_WAIVER = 'waiver';

    public const TYPES = [
        self::TYPE_DISCOUNT,
        self::TYPE_SCHOLARSHIP,
        self::TYPE_WAIVER,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'fee_invoice_id',
        'academic_year_id',
        'term_id',
        'type',
        'description',
        'amount',
        'status',
        'created_by_user_id',
        'approved_by_user_id',
        'decided_at',
        'decision_notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }
}
