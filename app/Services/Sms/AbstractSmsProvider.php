<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

abstract class AbstractSmsProvider implements SmsProviderInterface
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(protected array $config = []) {}

    abstract public function key(): string;

    public function enabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? false);
    }

    /**
     * Wrap the provider-specific HTTP call so that *any* exception is caught
     * and turned into a clean SmsResult failure. Failed sends MUST NOT crash
     * the parent request (audit log just marks them as failed).
     */
    protected function safeRequest(callable $callback): SmsResult
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            Log::warning('SMS provider request failed', [
                'provider' => $this->key(),
                'message' => $e->getMessage(),
            ]);

            return SmsResult::failure($e->getMessage());
        }
    }

    /**
     * Convenience HTTP client with a short timeout so a slow upstream cannot
     * pin a school's web request.
     */
    protected function http()
    {
        return Http::timeout(10)->connectTimeout(5);
    }

    protected function senderId(?string $override = null): string
    {
        return $override
            ?? ($this->config['sender_id'] ?? null)
            ?? (string) config('sms.sender_id', 'MYSCHOOL');
    }
}
