<?php

namespace App\Models;

use App\Models\Concerns\RecordsAuditTrail;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends BaseModel
{
    use RecordsAuditTrail, SoftDeletes;

    public const STATUS_PENDING = 'pending';

    public const STATUS_SUCCESSFUL = 'successful';

    public const STATUS_FAILED = 'failed';

    public const STATUS_REVERSED = 'reversed';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SUCCESSFUL,
        self::STATUS_FAILED,
        self::STATUS_REVERSED,
    ];

    public const CHANNEL_CASH = 'cash';

    public const CHANNEL_MOMO = 'momo';

    public const CHANNEL_BANK = 'bank';

    public const CHANNEL_CARD = 'card';

    public const CHANNEL_CHEQUE = 'cheque';

    public const CHANNEL_GATEWAY = 'gateway';

    public const CHANNELS = [
        self::CHANNEL_CASH,
        self::CHANNEL_MOMO,
        self::CHANNEL_BANK,
        self::CHANNEL_CARD,
        self::CHANNEL_CHEQUE,
        self::CHANNEL_GATEWAY,
    ];

    public const PROVIDER_MANUAL = 'manual';

    public const PROVIDER_HUBTEL = 'hubtel';

    public const PROVIDER_PAYSTACK = 'paystack';

    public const PROVIDER_FLUTTERWAVE = 'flutterwave';

    public const PROVIDER_EXPRESSPAY = 'expresspay';

    public const PROVIDERS = [
        self::PROVIDER_MANUAL,
        self::PROVIDER_HUBTEL,
        self::PROVIDER_PAYSTACK,
        self::PROVIDER_FLUTTERWAVE,
        self::PROVIDER_EXPRESSPAY,
    ];

    protected $fillable = [
        'tenant_id',
        'student_id',
        'fee_invoice_id',
        'received_by_user_id',
        'amount',
        'payment_channel',
        'reference',
        'payment_reference',
        'provider',
        'provider_reference',
        'status',
        'date',
        'receipt_id',
        'reversed_by_user_id',
        'reversed_at',
        'reversal_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
            'reversed_at' => 'datetime',
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

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by_user_id');
    }

    public function reverser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reversed_by_user_id');
    }

    public function isReversed(): bool
    {
        return $this->status === self::STATUS_REVERSED;
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESSFUL;
    }

    /**
     * Backwards-compatible accessor: many Phase 1 views, dashboards and
     * seeders still read `$payment->method`. Map it to `payment_channel`
     * so we don't have to chase every template at once.
     */
    public function getMethodAttribute(): ?string
    {
        return $this->attributes['payment_channel'] ?? null;
    }

    public function setMethodAttribute(?string $value): void
    {
        $this->attributes['payment_channel'] = $value;
    }
}
