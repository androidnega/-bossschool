<?php

namespace App\Services\Sms;

interface SmsProviderInterface
{
    /**
     * Short key for this provider, e.g. "hubtel". Used in
     * tenant_settings, config keys, and communication_logs.provider.
     */
    public function key(): string;

    /**
     * Whether the provider is currently enabled. Used by the dispatcher to
     * decide whether to actually attempt a send or just queue the log.
     */
    public function enabled(): bool;

    public function send(SmsMessage $message): SmsResult;
}
