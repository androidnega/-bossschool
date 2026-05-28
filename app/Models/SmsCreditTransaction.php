<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only ledger of SMS-credit changes per tenant.
 *
 * Sign convention:
 *   delta > 0  → credit added (purchase, manual grant, refund of a failed send)
 *   delta < 0  → credit consumed (an SMS we actually sent to a provider)
 *
 * `balance_after` is the running balance recorded at the time the row was
 * written, so we can re-verify the ledger by ordering by id and re-summing.
 */
class SmsCreditTransaction extends Model
{
    public const REASON_PURCHASE = 'purchase';

    public const REASON_MANUAL_GRANT = 'manual_grant';

    public const REASON_SMS_DEBIT = 'sms_debit';

    public const REASON_SMS_REFUND = 'sms_refund';

    public const REASON_ADJUSTMENT = 'adjustment';

    protected $fillable = [
        'tenant_id',
        'delta',
        'balance_after',
        'reason',
        'payment_transaction_id',
        'communication_log_id',
        'actor_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'delta' => 'integer',
            'balance_after' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function paymentTransaction(): BelongsTo
    {
        return $this->belongsTo(PaymentTransaction::class);
    }

    public function communicationLog(): BelongsTo
    {
        return $this->belongsTo(CommunicationLog::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
