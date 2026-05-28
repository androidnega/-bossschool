<?php

namespace App\Services\Sms;

use InvalidArgumentException;

/**
 * Resolves and caches SMS provider instances. Tests swap out a single
 * provider key by binding this registry as a singleton and replacing its
 * cached instance — see SmsDispatcherTest.
 */
class SmsProviderRegistry
{
    /** @var array<string, array<string, mixed>> */
    private array $config;

    /** @var array<string, SmsProviderInterface> */
    private array $resolved = [];

    /**
     * @param  array<string, array<string, mixed>>  $config  Map of provider key => provider config from config/sms.php
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function configured(): array
    {
        return $this->config;
    }

    /**
     * @return array<int, string>
     */
    public function enabledKeys(): array
    {
        return array_values(array_filter(array_keys($this->config), function (string $key): bool {
            $provider = $this->resolve($key);

            return $provider !== null && $provider->enabled();
        }));
    }

    public function resolve(string $key): ?SmsProviderInterface
    {
        if (isset($this->resolved[$key])) {
            return $this->resolved[$key];
        }

        $config = $this->config[$key] ?? null;
        if ($config === null) {
            return null;
        }

        $class = (string) ($config['class'] ?? '');
        if ($class === '' || ! class_exists($class)) {
            return null;
        }

        $instance = new $class($config);
        if (! $instance instanceof SmsProviderInterface) {
            throw new InvalidArgumentException("Provider class {$class} does not implement SmsProviderInterface.");
        }

        return $this->resolved[$key] = $instance;
    }

    /**
     * Replace (or inject) a provider instance for the given key. Tests use
     * this to plug in a FakeSmsProvider without touching env vars.
     */
    public function set(string $key, SmsProviderInterface $provider): void
    {
        $this->resolved[$key] = $provider;
        if (! isset($this->config[$key])) {
            $this->config[$key] = ['class' => $provider::class, 'enabled' => $provider->enabled()];
        }
    }
}
