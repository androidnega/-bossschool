<?php

namespace App\Services\Sms;

use App\Models\CommunicationLog;
use App\Models\PlatformSetting;
use App\Models\SmsCreditTransaction;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Per-tenant SMS credit ledger.
 *
 * Credits are integer counts of SMS messages remaining. Each successful debit
 * decrements `tenants.sms_credits_balance` and writes an append-only row to
 * `sms_credit_transactions`. Failed sends are refunded so a tenant is never
 * billed for an SMS the provider rejected.
 *
 * Pricing in GHS is held in a platform setting (`sms_price_pesewas`,
 * fractional pesewas allowed) and is multiplied at *purchase* time only —
 * once credits are in the balance, the dispatch path is a simple counter.
 */
class SmsCreditService
{
    /** Default unit price if the platform setting is missing. 0.38 pesewas / SMS. */
    public const DEFAULT_PRICE_PESEWAS = '0.38';

    public function balance(int $tenantId): int
    {
        return (int) (Tenant::query()->whereKey($tenantId)->value('sms_credits_balance') ?? 0);
    }

    /**
     * Per-SMS price in pesewas as a decimal string (so 0.38 stays exact).
     */
    public function pricePerSmsPesewas(): string
    {
        $value = PlatformSetting::getValue('sms_price_pesewas');
        if (! is_string($value) || trim($value) === '') {
            return self::DEFAULT_PRICE_PESEWAS;
        }

        return $value;
    }

    /** Convert pesewas (string, may be fractional) to GHS as a float. */
    public function pesewasToGhs(string $pesewas): float
    {
        return round(((float) $pesewas) / 100, 4);
    }

    /**
     * Compute the GHS cost of buying `$credits` SMSes at the current price.
     * Returns a 2-decimal float (the unit accepted by Paystack/payment_transactions).
     */
    public function quoteCostGhs(int $credits): float
    {
        if ($credits <= 0) {
            return 0.0;
        }

        return round($credits * ((float) $this->pricePerSmsPesewas()) / 100, 2);
    }

    /**
     * Take a GHS amount and figure out how many full SMSes that buys at the
     * current price. We round DOWN — never overpromise credits.
     */
    public function quoteCreditsForGhs(float $amountGhs): int
    {
        $pricePer = (float) $this->pricePerSmsPesewas();
        if ($pricePer <= 0) {
            return 0;
        }

        return (int) floor(($amountGhs * 100) / $pricePer);
    }

    /**
     * Debit ONE SMS from the tenant's balance and write a ledger row.
     * Returns true if the debit succeeded, false if balance was insufficient.
     *
     * Uses a row lock + check to make balance updates safe under concurrent
     * dispatchers (the queue worker + a synchronous send can race otherwise).
     */
    public function debitForSms(int $tenantId, CommunicationLog $log): bool
    {
        return (bool) DB::transaction(function () use ($tenantId, $log): bool {
            $tenant = Tenant::query()->whereKey($tenantId)->lockForUpdate()->first();
            if (! $tenant || (int) $tenant->sms_credits_balance < 1) {
                return false;
            }

            $tenant->forceFill([
                'sms_credits_balance' => (int) $tenant->sms_credits_balance - 1,
            ])->save();

            SmsCreditTransaction::query()->create([
                'tenant_id' => $tenantId,
                'delta' => -1,
                'balance_after' => (int) $tenant->sms_credits_balance,
                'reason' => SmsCreditTransaction::REASON_SMS_DEBIT,
                'communication_log_id' => $log->id,
            ]);

            return true;
        });
    }

    /**
     * Refund ONE SMS — used when the provider rejected the send AFTER we
     * already debited. Idempotent on (communication_log_id, REASON_SMS_REFUND).
     */
    public function refundForSms(int $tenantId, CommunicationLog $log): void
    {
        DB::transaction(function () use ($tenantId, $log): void {
            $alreadyRefunded = SmsCreditTransaction::query()
                ->where('communication_log_id', $log->id)
                ->where('reason', SmsCreditTransaction::REASON_SMS_REFUND)
                ->exists();
            if ($alreadyRefunded) {
                return;
            }

            $tenant = Tenant::query()->whereKey($tenantId)->lockForUpdate()->first();
            if (! $tenant) {
                return;
            }

            $tenant->forceFill([
                'sms_credits_balance' => (int) $tenant->sms_credits_balance + 1,
            ])->save();

            SmsCreditTransaction::query()->create([
                'tenant_id' => $tenantId,
                'delta' => 1,
                'balance_after' => (int) $tenant->sms_credits_balance,
                'reason' => SmsCreditTransaction::REASON_SMS_REFUND,
                'communication_log_id' => $log->id,
            ]);
        });
    }

    /**
     * Grant credits to a tenant from a Paystack purchase. Idempotent: the
     * caller must check by payment_transaction_id before invoking.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function grantPurchase(int $tenantId, int $credits, int $paymentTransactionId, array $metadata = []): void
    {
        if ($credits <= 0) {
            throw new RuntimeException('Cannot grant zero or negative SMS credits.');
        }

        DB::transaction(function () use ($tenantId, $credits, $paymentTransactionId, $metadata): void {
            $tenant = Tenant::query()->whereKey($tenantId)->lockForUpdate()->first();
            if (! $tenant) {
                throw new RuntimeException("Tenant {$tenantId} not found.");
            }

            $tenant->forceFill([
                'sms_credits_balance' => (int) $tenant->sms_credits_balance + $credits,
            ])->save();

            SmsCreditTransaction::query()->create([
                'tenant_id' => $tenantId,
                'delta' => $credits,
                'balance_after' => (int) $tenant->sms_credits_balance,
                'reason' => SmsCreditTransaction::REASON_PURCHASE,
                'payment_transaction_id' => $paymentTransactionId,
                'metadata' => $metadata ?: null,
            ]);
        });
    }

    /**
     * Manual grant by a SuperAdmin (e.g. demo top-up, goodwill credit).
     */
    public function manualGrant(int $tenantId, int $credits, int $actorId, ?string $note = null): void
    {
        if ($credits === 0) {
            return;
        }

        DB::transaction(function () use ($tenantId, $credits, $actorId, $note): void {
            $tenant = Tenant::query()->whereKey($tenantId)->lockForUpdate()->first();
            if (! $tenant) {
                throw new RuntimeException("Tenant {$tenantId} not found.");
            }

            $newBalance = max(0, (int) $tenant->sms_credits_balance + $credits);
            $tenant->forceFill(['sms_credits_balance' => $newBalance])->save();

            SmsCreditTransaction::query()->create([
                'tenant_id' => $tenantId,
                'delta' => $credits,
                'balance_after' => $newBalance,
                'reason' => $credits > 0
                    ? SmsCreditTransaction::REASON_MANUAL_GRANT
                    : SmsCreditTransaction::REASON_ADJUSTMENT,
                'actor_id' => $actorId,
                'metadata' => $note ? ['note' => $note] : null,
            ]);
        });
    }
}
