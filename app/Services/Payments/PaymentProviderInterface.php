<?php

namespace App\Services\Payments;

use Illuminate\Http\Request;

/**
 * Common surface every Ghana Mobile Money / card gateway must implement so
 * the webhook controller can stay provider-agnostic. Concrete providers
 * (Hubtel, Paystack, Flutterwave, ExpressPay) live next to this file.
 *
 * The first capability we wire is signature verification, because the
 * audit explicitly demands that unsigned webhooks be rejected.
 */
interface PaymentProviderInterface
{
    /** Short, lowercase identifier (matches Payment::PROVIDER_* constants). */
    public function key(): string;

    /**
     * Compute the expected signature for a webhook payload and compare it
     * against whatever header the provider uses. Concrete implementations
     * must be constant-time. Returning false here makes the webhook
     * controller reject the call with HTTP 401 — no Payment is created or
     * updated.
     */
    public function verifyWebhookSignature(Request $request): bool;

    /**
     * Parse a verified webhook payload into a normalised array the rest of
     * the system can consume. Returning an empty array signals "nothing to
     * do" (e.g. a heartbeat or unrelated event).
     *
     * Expected keys (when applicable):
     *  - provider_reference (string)
     *  - amount (float)
     *  - currency (string)
     *  - status (one of Payment::STATUSES)
     *  - payment_channel (one of Payment::CHANNELS)
     *  - student_external_id (string|null) — provider-side student/customer ref
     *  - raw (array) — the raw payload for storage/debugging
     *
     * @return array<string, mixed>
     */
    public function parseWebhookEvent(Request $request): array;
}
