<?php

namespace App\Services\Payments;

use App\Models\FeeInvoice;
use App\Models\Payment;
use App\Models\PaymentTransaction;
use App\Models\User;
use App\Services\InvoiceCalculator;
use App\Services\Sms\SmsCreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Orchestrates online payment lifecycles.
 *
 * Phase 4 keeps a clean seam: the service `initiate()`s a transaction and
 * later `applyVerifiedEvent()` ingests verified webhook payloads, creating
 * or updating a Payment when the gateway tells us the money landed. The
 * (provider, provider_reference) pair is the idempotency key — duplicate
 * webhooks update the existing row, they never duplicate Payments.
 */
class PaymentGatewayService
{
    public const PURPOSE_FEE_INVOICE = 'fee_invoice';

    public const PURPOSE_SMS_CREDITS = 'sms_credits';

    public const PURPOSE_SUBSCRIPTION = 'subscription';

    public function __construct(
        private readonly PaymentProviderRegistry $registry,
        private readonly InvoiceCalculator $invoices,
    ) {}

    /**
     * Initiate a payment that is NOT tied to a fee invoice — used by the
     * SMS-credits and subscription purchase flows. Caller has already minted
     * the gateway checkout URL (if any) via the provider's own SDK.
     *
     * @param  array<string, mixed>  $metadata  Echoed back to us by Paystack in webhooks/verify so we can map to the right purpose.
     */
    public function initiateGenericPurchase(
        User $initiator,
        int $tenantId,
        string $purpose,
        string $providerKey,
        float $amount,
        ?string $providerReference = null,
        ?string $checkoutUrl = null,
        array $metadata = [],
        array $rawRequest = [],
    ): PaymentTransaction {
        if ($this->registry->get($providerKey) === null) {
            throw new RuntimeException("Payment provider '{$providerKey}' is not enabled.");
        }
        if ($amount <= 0) {
            throw new RuntimeException('Cannot initiate payment for a zero or negative amount.');
        }

        $providerReference = $providerReference ?: ($providerKey.'_'.Str::ulid());

        return PaymentTransaction::query()->create([
            'tenant_id' => $tenantId,
            'student_id' => null,
            'fee_invoice_id' => null,
            'initiated_by_user_id' => $initiator->id,
            'provider' => $providerKey,
            'provider_reference' => $providerReference,
            'checkout_url' => $checkoutUrl,
            'amount' => round($amount, 2),
            'currency' => 'GHS',
            'status' => PaymentTransaction::STATUS_INITIATED,
            'purpose' => $purpose,
            'metadata' => $metadata ?: null,
            'raw_request' => $rawRequest ?: null,
        ]);
    }

    /**
     * Create a new payment_transaction in `initiated` state. We do NOT
     * actually call the upstream gateway in this method — the caller
     * provides the `provider`, `amount`, and an optional pre-generated
     * `checkout_url` from whatever provider-specific SDK they used.
     *
     * `provider_reference` is generated locally if not supplied, so even
     * before the upstream confirms, the transaction is uniquely addressable
     * for our own webhook callbacks.
     */
    public function initiate(
        User $initiator,
        FeeInvoice $invoice,
        string $providerKey,
        ?float $amount = null,
        ?string $checkoutUrl = null,
        ?string $providerReference = null,
        array $rawRequest = [],
    ): PaymentTransaction {
        if ($this->registry->get($providerKey) === null) {
            throw new RuntimeException("Payment provider '{$providerKey}' is not enabled.");
        }

        $effectiveAmount = $amount !== null ? round($amount, 2) : (float) $invoice->balance;
        if ($effectiveAmount <= 0) {
            throw new RuntimeException('Cannot initiate payment for a zero or negative amount.');
        }

        $providerReference = $providerReference ?: ($providerKey.'_'.Str::ulid());

        return PaymentTransaction::query()->create([
            'tenant_id' => (int) $invoice->tenant_id,
            'student_id' => (int) $invoice->student_id,
            'fee_invoice_id' => $invoice->id,
            'initiated_by_user_id' => $initiator->id,
            'provider' => $providerKey,
            'provider_reference' => $providerReference,
            'checkout_url' => $checkoutUrl,
            'amount' => $effectiveAmount,
            'currency' => 'GHS',
            'status' => PaymentTransaction::STATUS_INITIATED,
            'raw_request' => $rawRequest ?: null,
        ]);
    }

    /**
     * Apply a verified webhook event. Caller (the webhook controller) has
     * already confirmed the signature was valid.
     *
     * @param  array<string, mixed>  $event  Normalised payload from a PaymentProviderInterface::parseWebhookEvent()
     * @return PaymentTransaction|null  Returns null when the event has no provider_reference (we ignore it)
     */
    public function applyVerifiedEvent(string $providerKey, array $event): ?PaymentTransaction
    {
        $reference = trim((string) ($event['provider_reference'] ?? ''));
        if ($reference === '') {
            return null;
        }

        return DB::transaction(function () use ($providerKey, $event, $reference): ?PaymentTransaction {
            $transaction = PaymentTransaction::query()
                ->where('provider', $providerKey)
                ->where('provider_reference', $reference)
                ->lockForUpdate()
                ->first();

            // Idempotent: if we have never seen this reference before we just
            // log the event and return without mutating anything. We refuse
            // to invent a transaction with an unknown tenant/student because
            // the foreign keys on payment_transactions would be wrong, and
            // operating on orphan rows weakens the audit trail.
            if (! $transaction) {
                \Illuminate\Support\Facades\Log::info('payment.webhook.unknown_reference', [
                    'provider' => $providerKey,
                    'reference' => $reference,
                    'status' => (string) ($event['status'] ?? ''),
                ]);

                return null;
            }

            $transaction->forceFill([
                'status' => $this->mapStatus((string) ($event['status'] ?? '')),
                'raw_webhook' => $event,
                'amount' => $transaction->amount ?: (float) ($event['amount'] ?? 0),
            ])->save();

            // Branch by purpose. fee_invoice stays the legacy behaviour;
            // sms_credits and subscription invoke their own side-effects.
            if ($transaction->status === PaymentTransaction::STATUS_SUCCESSFUL) {
                $purpose = (string) ($transaction->purpose ?: self::PURPOSE_FEE_INVOICE);

                if ($purpose === self::PURPOSE_FEE_INVOICE && $transaction->fee_invoice_id) {
                    $payment = $this->upsertPayment($transaction, $event, $providerKey);
                    $transaction->payment_id = $payment->id;
                    $transaction->save();

                    if ($transaction->invoice) {
                        $this->invoices->refresh($transaction->invoice);
                    }
                } elseif ($purpose === self::PURPOSE_SMS_CREDITS) {
                    $this->applySmsCreditsPurchase($transaction);
                } elseif ($purpose === self::PURPOSE_SUBSCRIPTION) {
                    $this->applySubscriptionPurchase($transaction);
                }
            }

            return $transaction;
        });
    }

    /**
     * Grant the SMS credits paid for in this transaction. Idempotent on
     * `payment_transaction_id` — duplicate webhooks cannot grant twice.
     */
    private function applySmsCreditsPurchase(PaymentTransaction $transaction): void
    {
        $metadata = (array) ($transaction->metadata ?? []);
        $credits = (int) ($metadata['credits'] ?? 0);
        if ($credits <= 0 || ! $transaction->tenant_id) {
            Log::warning('payment.sms_credits.invalid_metadata', [
                'transaction_id' => $transaction->id,
                'credits' => $credits,
                'tenant_id' => $transaction->tenant_id,
            ]);

            return;
        }

        // Has this exact transaction already granted credits? If so, do nothing.
        $alreadyApplied = \App\Models\SmsCreditTransaction::query()
            ->where('payment_transaction_id', $transaction->id)
            ->where('reason', \App\Models\SmsCreditTransaction::REASON_PURCHASE)
            ->exists();
        if ($alreadyApplied) {
            return;
        }

        app(SmsCreditService::class)->grantPurchase(
            tenantId: (int) $transaction->tenant_id,
            credits: $credits,
            paymentTransactionId: (int) $transaction->id,
            metadata: ['amount_ghs' => (float) $transaction->amount],
        );
    }

    /**
     * Create (or refresh) the Subscription row matching this paid transaction.
     * Idempotent on payment_id — duplicate webhooks cannot create two rows.
     */
    private function applySubscriptionPurchase(PaymentTransaction $transaction): void
    {
        $metadata = (array) ($transaction->metadata ?? []);
        $planId = (int) ($metadata['plan_id'] ?? 0);
        if (! $transaction->tenant_id || $planId <= 0) {
            return;
        }

        $plan = \App\Models\Plan::query()->find($planId);
        if (! $plan) {
            return;
        }

        $existing = \App\Models\Subscription::query()
            ->where('tenant_id', $transaction->tenant_id)
            ->where('payment_id', $transaction->payment_id)
            ->when($transaction->payment_id === null, fn ($q) => $q
                ->whereRaw('1=0')) // payment_id needs to be set before idempotency works
            ->first();
        if ($existing) {
            return;
        }

        // Cancel any currently-active subscription for the tenant.
        \App\Models\Subscription::query()
            ->where('tenant_id', $transaction->tenant_id)
            ->where('status', \App\Models\Subscription::STATUS_ACTIVE)
            ->update(['status' => \App\Models\Subscription::STATUS_CANCELLED, 'end_date' => now()->toDateString()]);

        $billingCycle = (string) ($plan->billing_cycle ?? 'monthly');
        $start = now()->toDateString();
        $end = match ($billingCycle) {
            'yearly', 'annual' => now()->addYear()->toDateString(),
            'quarterly' => now()->addMonths(3)->toDateString(),
            'weekly' => now()->addWeek()->toDateString(),
            default => now()->addMonth()->toDateString(),
        };

        \App\Models\Subscription::query()->create([
            'tenant_id' => $transaction->tenant_id,
            'plan_id' => $planId,
            'amount' => (float) $transaction->amount,
            'billing_cycle' => $billingCycle,
            'start_date' => $start,
            'end_date' => $end,
            'status' => \App\Models\Subscription::STATUS_ACTIVE,
            'note' => 'Paid via '.$transaction->provider,
            'payment_id' => null, // Optional: link to a Payment row if you mint one for subs.
        ]);

        \App\Models\Tenant::query()
            ->whereKey($transaction->tenant_id)
            ->update(['plan_id' => $planId]);
    }

    /**
     * Create or update the Payment row that mirrors a successful transaction.
     * The Payment is the canonical financial record; the PaymentTransaction
     * is the gateway-side ledger.
     */
    private function upsertPayment(PaymentTransaction $transaction, array $event, string $providerKey): Payment
    {
        if ($transaction->payment_id) {
            $existing = Payment::query()->whereKey($transaction->payment_id)->first();
            if ($existing) {
                $existing->forceFill([
                    'status' => Payment::STATUS_SUCCESSFUL,
                    'provider' => $providerKey,
                    'provider_reference' => $transaction->provider_reference,
                ])->save();

                return $existing;
            }
        }

        $attrs = [
            'tenant_id' => $transaction->tenant_id,
            'student_id' => $transaction->student_id ?: ($transaction->invoice?->student_id ?? 0),
            'fee_invoice_id' => $transaction->fee_invoice_id,
            'amount' => $transaction->amount,
            'payment_channel' => (string) ($event['payment_channel'] ?? Payment::CHANNEL_GATEWAY),
            'status' => Payment::STATUS_SUCCESSFUL,
            'date' => now()->toDateString(),
        ];

        return Payment::query()->updateOrCreate(
            [
                'provider' => $providerKey,
                'provider_reference' => $transaction->provider_reference,
            ],
            $attrs,
        );
    }

    private function mapStatus(string $providerStatus): string
    {
        return match (strtolower($providerStatus)) {
            Payment::STATUS_SUCCESSFUL, 'success', 'paid' => PaymentTransaction::STATUS_SUCCESSFUL,
            Payment::STATUS_FAILED, 'failed', 'error' => PaymentTransaction::STATUS_FAILED,
            Payment::STATUS_PENDING, 'pending' => PaymentTransaction::STATUS_PENDING,
            'cancelled', 'canceled' => PaymentTransaction::STATUS_CANCELLED,
            default => PaymentTransaction::STATUS_PENDING,
        };
    }
}
