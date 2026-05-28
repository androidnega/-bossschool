<?php

namespace App\Services\Payments;

use App\Models\PlatformSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Paystack integration: outbound charge initiation + transaction verification
 * + (inherited) HMAC sha512 webhook verification.
 *
 * Credential strategy:
 *   - First we look in the `platform_settings` key/value store
 *     (paystack_secret_key, paystack_public_key) — this is the SuperAdmin
 *     UI's source of truth.
 *   - As a fallback we honour PAYMENTS_PAYSTACK_SECRET in env (legacy).
 *
 * Money strategy:
 *   - Paystack works in the lowest currency unit ("kobo" or "pesewas").
 *     1 GHS = 100 pesewas, so amount_in_pesewas = amount_in_ghs * 100.
 *   - We pass integer pesewas to Paystack and store our authoritative
 *     amount in decimal GHS on payment_transactions.amount.
 */
class PaystackProvider extends AbstractPaymentProvider
{
    public const BASE_URL = 'https://api.paystack.co';

    public function key(): string
    {
        return \App\Models\Payment::PROVIDER_PAYSTACK;
    }

    protected function signatureHeaderName(): string
    {
        return 'X-Paystack-Signature';
    }

    protected function signingAlgo(): string
    {
        return 'sha512';
    }

    /**
     * Credentials are resolved at call time so a SuperAdmin can rotate the
     * key in the UI without a process restart. Fall back to the config-file
     * value (loaded from env) only if no DB row is set.
     */
    protected function signingSecret(): ?string
    {
        $fromDb = PlatformSetting::getValue('paystack_secret_key');
        if (is_string($fromDb) && $fromDb !== '') {
            return $fromDb;
        }

        return $this->config['secret'] ?? null;
    }

    public function publicKey(): ?string
    {
        $fromDb = PlatformSetting::getValue('paystack_public_key');
        if (is_string($fromDb) && $fromDb !== '') {
            return $fromDb;
        }

        return $this->config['public_key'] ?? null;
    }

    /**
     * Whether the integration is fully configured (secret present + the
     * `enabled` flag on the platform setting). The webhook controller and
     * the registry already gate on `enabled`; this method is a convenience
     * for UI guards ("you cannot initiate Paystack until you set the keys").
     */
    public function isConfigured(): bool
    {
        $secret = $this->signingSecret();

        return is_string($secret) && $secret !== '';
    }

    /**
     * Call Paystack's `/transaction/initialize` endpoint, returning the
     * `authorization_url` the user should be redirected to plus the
     * `reference` we should track. Throws on any non-2xx response.
     *
     * @param  array<string, mixed>  $metadata  Arbitrary key/value pairs Paystack will
     *                                          echo back to us in webhooks and verify-responses.
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initializeTransaction(
        string $email,
        int $amountInPesewas,
        string $callbackUrl,
        string $reference,
        array $metadata = [],
        string $currency = 'GHS',
    ): array {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Paystack is not configured. Set the secret key in Platform Settings.');
        }
        if ($amountInPesewas <= 0) {
            throw new RuntimeException('Paystack amount must be greater than zero.');
        }

        $response = $this->httpClient()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $amountInPesewas,
            'currency' => $currency,
            'callback_url' => $callbackUrl,
            'reference' => $reference,
            'metadata' => $metadata,
        ]);

        if (! $response->successful()) {
            Log::error('payment.paystack.initialize.failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'reference' => $reference,
            ]);
            throw new RuntimeException(sprintf(
                'Paystack initialize failed (%d): %s',
                $response->status(),
                (string) ($response->json('message') ?? 'Unknown error')
            ));
        }

        $data = (array) ($response->json('data') ?? []);
        if (empty($data['authorization_url']) || empty($data['reference'])) {
            throw new RuntimeException('Paystack initialize returned an incomplete payload.');
        }

        return [
            'authorization_url' => (string) $data['authorization_url'],
            'access_code' => (string) ($data['access_code'] ?? ''),
            'reference' => (string) $data['reference'],
        ];
    }

    /**
     * Server-side verification of a Paystack reference. Used both by the
     * synchronous callback (when the user is redirected back to us) and as
     * a safety net for the webhook handler.
     *
     * @return array{status: string, amount_kobo: int, amount: float, currency: string, reference: string, channel: ?string, raw: array<string, mixed>}
     */
    public function verifyTransaction(string $reference): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('Paystack is not configured.');
        }

        $response = $this->httpClient()->get('/transaction/verify/'.rawurlencode($reference));

        if (! $response->successful()) {
            Log::warning('payment.paystack.verify.failed', [
                'status' => $response->status(),
                'reference' => $reference,
            ]);
            throw new RuntimeException(sprintf(
                'Paystack verify failed (%d).',
                $response->status()
            ));
        }

        $data = (array) ($response->json('data') ?? []);
        $statusRaw = strtolower((string) ($data['status'] ?? ''));
        $amountKobo = (int) ($data['amount'] ?? 0);

        return [
            'status' => $this->normaliseStatus($statusRaw),
            'amount_kobo' => $amountKobo,
            'amount' => round($amountKobo / 100, 2),
            'currency' => (string) ($data['currency'] ?? 'GHS'),
            'reference' => (string) ($data['reference'] ?? $reference),
            'channel' => $data['channel'] ?? null,
            'raw' => $data,
        ];
    }

    public function parseWebhookEvent(Request $request): array
    {
        $payload = $request->json()->all() ?: [];
        // Paystack wraps the actual transaction under `data` and signals the
        // event type via `event` (e.g. charge.success). Fall through to the
        // base implementation if it's already a flat shape (e.g. in tests).
        $data = isset($payload['data']) && is_array($payload['data']) ? $payload['data'] : $payload;

        $amountKobo = isset($data['amount']) ? (int) $data['amount'] : 0;
        $metadata = isset($data['metadata']) && is_array($data['metadata']) ? $data['metadata'] : [];

        return [
            'provider_reference' => (string) ($data['reference'] ?? ''),
            'amount' => round($amountKobo / 100, 2),
            'amount_kobo' => $amountKobo,
            'currency' => (string) ($data['currency'] ?? 'GHS'),
            'status' => $this->normaliseStatus($data['status'] ?? ($payload['event'] ?? null)),
            'payment_channel' => (string) ($data['channel'] ?? 'card'),
            'student_external_id' => $metadata['student_external_id'] ?? null,
            'purpose' => isset($metadata['purpose']) ? (string) $metadata['purpose'] : null,
            'metadata' => $metadata,
            'raw' => $payload,
        ];
    }

    protected function httpClient(): PendingRequest
    {
        return Http::withToken((string) $this->signingSecret())
            ->acceptJson()
            ->asJson()
            ->baseUrl(self::BASE_URL)
            ->timeout(15);
    }
}
