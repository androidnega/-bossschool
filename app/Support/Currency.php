<?php

namespace App\Support;

final class Currency
{
    /**
     * Format an amount as Ghana cedis (GHS) for display.
     */
    public static function cedis(float|int|string|null $amount, int $decimals = 2): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;

        return 'GH₵ '.number_format($value, $decimals);
    }
}
