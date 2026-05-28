<?php

namespace App\Services\Payments;

/**
 * Resolve PaymentProviderInterface implementations by key. Reads
 * `config('payments.providers')` so individual tenants / environments can
 * disable a provider by omitting its config block, and so the
 * webhook controller can stay completely agnostic.
 */
class PaymentProviderRegistry
{
    /** @var array<string, PaymentProviderInterface> */
    private array $cache = [];

    public function __construct(private readonly array $config = []) {}

    public function get(string $key): ?PaymentProviderInterface
    {
        $key = strtolower($key);
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $cfg = $this->config[$key] ?? null;
        if (! is_array($cfg) || ($cfg['enabled'] ?? true) === false) {
            return null;
        }

        $provider = match ($key) {
            \App\Models\Payment::PROVIDER_HUBTEL => new HubtelProvider($cfg),
            \App\Models\Payment::PROVIDER_PAYSTACK => new PaystackProvider($cfg),
            \App\Models\Payment::PROVIDER_FLUTTERWAVE => new FlutterwaveProvider($cfg),
            \App\Models\Payment::PROVIDER_EXPRESSPAY => new ExpressPayProvider($cfg),
            default => null,
        };

        if ($provider) {
            $this->cache[$key] = $provider;
        }

        return $provider;
    }

    /** @return array<int, string> */
    public function enabledKeys(): array
    {
        $keys = [];
        foreach ($this->config as $key => $cfg) {
            if (is_array($cfg) && ($cfg['enabled'] ?? true) !== false) {
                $keys[] = strtolower((string) $key);
            }
        }

        return $keys;
    }
}
