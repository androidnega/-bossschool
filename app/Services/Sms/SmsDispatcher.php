<?php

namespace App\Services\Sms;

use App\Models\CommunicationLog;
use App\Services\TenantSettings;
use App\Support\GhanaPhone;
use Illuminate\Support\Facades\Log;

/**
 * Take an already-persisted communication_logs row (channel = sms) and
 * actually send it through the configured provider.
 *
 * The dispatcher never throws on send failure: it just updates the row's
 * status and error_message and returns. This is the seam that the message
 * controller, fee reminder flow, and attendance absence alerts all share.
 */
class SmsDispatcher
{
    public function __construct(
        private readonly SmsProviderRegistry $registry,
        private readonly TenantSettings $tenantSettings,
        private readonly SmsCreditService $credits,
    ) {}

    /**
     * Dispatch a single CommunicationLog row. The row must already exist;
     * caller is expected to have stamped tenant_id, recipient_phone, body, etc.
     */
    public function dispatch(CommunicationLog $log): CommunicationLog
    {
        if ($log->channel !== CommunicationLog::CHANNEL_SMS) {
            return $log;
        }

        if (in_array($log->status, [CommunicationLog::STATUS_SENT, CommunicationLog::STATUS_FAILED], true)) {
            return $log;
        }

        $phone = trim((string) $log->recipient_phone);
        if ($phone === '') {
            $this->markSkipped($log, 'No phone number on file');

            return $log;
        }

        $phone = GhanaPhone::normalize($phone) ?? $phone;

        $providerKey = $this->resolveProviderKey($log);
        $provider = $providerKey ? $this->registry->resolve($providerKey) : null;

        if ($provider === null) {
            $this->markSkipped($log, 'No SMS provider configured');

            return $log;
        }

        if (! $provider->enabled()) {
            $this->markQueued($log, $provider->key(), 'Provider disabled');

            return $log;
        }

        if ((bool) config('sms.sandbox', false)) {
            $log->forceFill([
                'status' => CommunicationLog::STATUS_SENT,
                'provider' => $provider->key(),
                'provider_reference' => 'sandbox_'.uniqid(),
                'sent_at' => now(),
                'error_message' => null,
            ])->save();

            return $log;
        }

        // Debit one SMS credit BEFORE talking to the provider. If the balance
        // is zero we never send (and never spend the operator's bulk-SMS
        // funds upstream). Tenant-less logs (platform notifications) skip
        // the credit gate entirely. The whole step can be turned off via
        // config('sms.bill_via_credits') for environments that bill another
        // way.
        $billViaCredits = (bool) config('sms.bill_via_credits', true);
        $debited = false;
        if ($billViaCredits && $log->tenant_id) {
            $debited = $this->credits->debitForSms((int) $log->tenant_id, $log);
            if (! $debited) {
                $log->forceFill([
                    'status' => CommunicationLog::STATUS_FAILED,
                    'provider' => $provider->key(),
                    'error_message' => 'Insufficient SMS credits. Top up to send messages.',
                ])->save();

                Log::warning('SMS dispatch blocked: insufficient credits', [
                    'log_id' => $log->id,
                    'tenant_id' => $log->tenant_id,
                ]);

                return $log;
            }
        }

        $result = $provider->send(new SmsMessage(
            to: $phone,
            body: (string) $log->message,
        ));

        if ($result->success) {
            $log->forceFill([
                'status' => CommunicationLog::STATUS_SENT,
                'provider' => $provider->key(),
                'provider_reference' => $result->providerReference,
                'sent_at' => now(),
                'error_message' => null,
            ])->save();
        } else {
            // The provider rejected the send. Refund the credit we just
            // debited so the tenant isn't billed for a message that didn't
            // go out.
            if ($debited && $log->tenant_id) {
                $this->credits->refundForSms((int) $log->tenant_id, $log);
            }

            $log->forceFill([
                'status' => CommunicationLog::STATUS_FAILED,
                'provider' => $provider->key(),
                'error_message' => $result->errorMessage,
            ])->save();

            Log::warning('SMS dispatch failed', [
                'log_id' => $log->id,
                'tenant_id' => $log->tenant_id,
                'provider' => $provider->key(),
                'error' => $result->errorMessage,
            ]);
        }

        return $log;
    }

    /**
     * Convenience helper to create a CommunicationLog and immediately dispatch it.
     *
     * @param  array<string, mixed>  $attributes  Anything that fits CommunicationLog::$fillable. tenant_id, channel and purpose are filled if missing.
     */
    public function sendNow(array $attributes): CommunicationLog
    {
        $attributes = array_merge([
            'tenant_id' => (int) (auth()->user()?->tenant_id ?? 0),
            'channel' => CommunicationLog::CHANNEL_SMS,
            'purpose' => CommunicationLog::PURPOSE_GENERAL,
            'status' => CommunicationLog::STATUS_QUEUED,
        ], $attributes);

        $log = CommunicationLog::query()->create($attributes);

        return $this->dispatch($log);
    }

    /**
     * Dispatch every still-queued SMS row for the given tenant. Used by the
     * UI "Send queued messages" action.
     */
    public function dispatchQueued(int $tenantId, int $limit = 100): int
    {
        $rows = CommunicationLog::query()
            ->where('tenant_id', $tenantId)
            ->where('channel', CommunicationLog::CHANNEL_SMS)
            ->where('status', CommunicationLog::STATUS_QUEUED)
            ->orderBy('id')
            ->limit($limit)
            ->get();

        foreach ($rows as $row) {
            $this->dispatch($row);
        }

        return $rows->count();
    }

    private function markSkipped(CommunicationLog $log, string $reason): void
    {
        $log->forceFill([
            'status' => CommunicationLog::STATUS_SKIPPED,
            'error_message' => $reason,
        ])->save();
    }

    private function markQueued(CommunicationLog $log, string $providerKey, string $reason): void
    {
        $log->forceFill([
            'status' => CommunicationLog::STATUS_QUEUED,
            'provider' => $providerKey,
            'error_message' => $reason,
        ])->save();
    }

    private function resolveProviderKey(CommunicationLog $log): ?string
    {
        if (! empty($log->provider)) {
            return $log->provider;
        }

        if ($log->tenant_id) {
            $key = (string) $this->tenantSettings->get((int) $log->tenant_id, 'default_sms_provider', '');
            if ($key !== '') {
                return $key;
            }
        }

        return (string) config('sms.default', 'log') ?: null;
    }
}
