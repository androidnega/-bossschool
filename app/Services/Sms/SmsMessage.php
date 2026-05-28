<?php

namespace App\Services\Sms;

/**
 * Immutable value object passed from the dispatcher down to provider clients.
 * Providers must not mutate it; they only read the fields and return an
 * {@see SmsResult}.
 */
final class SmsMessage
{
    public function __construct(
        public readonly string $to,
        public readonly string $body,
        public readonly ?string $senderId = null,
    ) {}
}
