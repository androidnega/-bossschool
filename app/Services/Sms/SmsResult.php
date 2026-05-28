<?php

namespace App\Services\Sms;

/**
 * Result returned by a provider after attempting to send an SMS.
 * `providerReference` is whatever opaque ID the upstream gave us so we can
 * reconcile delivery status later through provider callbacks or queries.
 */
final class SmsResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $providerReference = null,
        public readonly ?string $errorMessage = null,
    ) {}

    public static function ok(?string $providerReference = null): self
    {
        return new self(true, $providerReference, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, null, $errorMessage);
    }
}
