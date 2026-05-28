<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

/**
 * Shared helpers for HMAC-based signature verification. Each provider's
 * canonical signing string and header name is slightly different; concrete
 * subclasses define those, this class handles the constant-time compare.
 */
abstract class AbstractPaymentProvider implements PaymentProviderInterface
{
    public function __construct(protected readonly array $config = []) {}

    abstract public function key(): string;

    /** Header that carries the provider's signature. */
    abstract protected function signatureHeaderName(): string;

    /** Algorithm passed to hash_hmac (typically sha256 or sha512). */
    protected function signingAlgo(): string
    {
        return 'sha256';
    }

    /** Secret key for HMAC signing. */
    protected function signingSecret(): ?string
    {
        return $this->config['secret'] ?? null;
    }

    /**
     * Default signing payload is the raw request body. Providers that pre-
     * hash certain fields (Paystack does this) override this method.
     */
    protected function signingPayload(Request $request): string
    {
        return $request->getContent() ?: '';
    }

    public function verifyWebhookSignature(Request $request): bool
    {
        $secret = $this->signingSecret();
        if (! is_string($secret) || $secret === '') {
            // No secret configured: reject by default. Operators MUST set the
            // secret before enabling the webhook.
            return false;
        }

        $provided = (string) $request->header($this->signatureHeaderName(), '');
        if ($provided === '') {
            return false;
        }

        $expected = hash_hmac($this->signingAlgo(), $this->signingPayload($request), $secret);

        return hash_equals($expected, $provided);
    }

    public function parseWebhookEvent(Request $request): array
    {
        $payload = $request->json()->all() ?: [];

        return [
            'provider_reference' => (string) ($payload['reference'] ?? $payload['transaction_id'] ?? ''),
            'amount' => isset($payload['amount']) ? (float) $payload['amount'] : 0.0,
            'currency' => (string) ($payload['currency'] ?? 'GHS'),
            'status' => $this->normaliseStatus($payload['status'] ?? null),
            'payment_channel' => (string) ($payload['channel'] ?? 'momo'),
            'student_external_id' => $payload['customer_ref'] ?? $payload['student_ref'] ?? null,
            'raw' => $payload,
        ];
    }

    protected function normaliseStatus(mixed $status): string
    {
        $map = [
            'success' => \App\Models\Payment::STATUS_SUCCESSFUL,
            'successful' => \App\Models\Payment::STATUS_SUCCESSFUL,
            'paid' => \App\Models\Payment::STATUS_SUCCESSFUL,
            'failed' => \App\Models\Payment::STATUS_FAILED,
            'pending' => \App\Models\Payment::STATUS_PENDING,
            'reversed' => \App\Models\Payment::STATUS_REVERSED,
            'refunded' => \App\Models\Payment::STATUS_REVERSED,
        ];

        $key = is_string($status) ? strtolower($status) : '';

        return $map[$key] ?? \App\Models\Payment::STATUS_PENDING;
    }
}
