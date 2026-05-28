<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PlatformSetting extends Model
{
    public const CACHE_KEY = 'platform_settings_v1';

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public static function booted(): void
    {
        static::saved(function (): void {
            Cache::forget(self::CACHE_KEY);
        });

        static::deleted(function (): void {
            Cache::forget(self::CACHE_KEY);
        });
    }

    /**
     * @return array<string, string|null>
     */
    public static function allCached(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function (): array {
            return self::query()->pluck('value', 'key')->all();
        });
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function getValue(string $key, ?string $default = null): ?string
    {
        return self::allCached()[$key] ?? $default;
    }

    public static function getBool(string $key, bool $default = false): bool
    {
        $v = self::getValue($key);

        if ($v === null) {
            return $default;
        }

        return in_array(strtolower((string) $v), ['1', 'true', 'yes', 'on'], true);
    }

    public static function getInt(string $key, int $default = 0): int
    {
        $v = self::getValue($key);

        return $v !== null && is_numeric($v) ? (int) $v : $default;
    }
}
