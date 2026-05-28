<?php

use App\Support\Currency;

if (! function_exists('cedis')) {
    function cedis(float|int|string|null $amount, int $decimals = 2): string
    {
        return Currency::cedis($amount, $decimals);
    }
}
