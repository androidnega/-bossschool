<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends BaseModel
{
    use RecordsAuditTrail;

    public const STATUS_INITIATED = 'initiated';

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESSFUL = 'successful';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_INITIATED,
        self::STATUS_PENDING,
        self::STATUS_SUCCESSFUL,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
    ];

    /** Raw JSON columns we never want bleeding into audit metadata. */
    public array $auditHidden = ['raw_request', 'raw_response', 'raw_webhook'];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'fee_invoice_id',
        'payment_id',
        'initiated_by_user_id',
        'provider',
        'provider_reference',
        'checkout_url',
        'amount',
        'currency',
        'status',
        'purpose',
        'metadata',
        'raw_request',
        'raw_response',
        'raw_webhook',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'metadata' => 'array',
            'raw_request' => 'array',
            'raw_response' => 'array',
            'raw_webhook' => 'array',
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }
}
