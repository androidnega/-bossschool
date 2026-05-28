<?php

namespace App\Support;

/**
 * Single source of truth for parsing + canonicalising Ghanaian phone numbers.
 *
 * Used by:
 *  - the GhanaPhone validation rule (accepts any input that {@see parse()}
 *    can recognise)
 *  - mutators on Student.parent_phone, Staff.phone, School.phone (store the
 *    {@see normalize()}d +233XXXXXXXXX form so search/dedupe is consistent)
 *
 * Local subscriber number is always 9 digits and must start 2-9
 * (NCA-assigned mobile prefixes 20/23/24/25/26/27/28/50/53-57/59 and
 * landlines starting 30/32/33/34/35/36/37/38/39/40-49). We deliberately
 * accept the full 2-9 range so the rule is forward-compatible with new
 * prefixes Ghana NCA hands out.
 */
final class GhanaPhone
{
    /**
     * Strip formatting and split into +233-prefix and 9-digit subscriber.
     *
     * Returns null if the input is unparseable (does not start with a known
     * prefix or the subscriber number is not 9 digits starting 2-9).
     *
     * @return array{country: string, subscriber: string}|null
     */
    public static function parse(?string $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        $digits = (string) preg_replace('/[\s\-\(\)\.]/', '', $trim);

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '+233')) {
            $local = substr($digits, 4);
        } elseif (str_starts_with($digits, '233')) {
            $local = substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $local = substr($digits, 1);
        } else {
            return null;
        }

        if (! preg_match('/^[2-9]\d{8}$/', $local)) {
            return null;
        }

        return ['country' => '+233', 'subscriber' => $local];
    }

    public static function isValid(?string $value): bool
    {
        return self::parse($value) !== null;
    }

    /**
     * Return the canonical +233XXXXXXXXX form, or null if the input is empty.
     * If the input is not parseable, returns the original string untouched
     * so downstream validation can surface a clean error message.
     */
    public static function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trim = trim($value);
        if ($trim === '') {
            return null;
        }

        $parsed = self::parse($value);

        if ($parsed === null) {
            return $value;
        }

        return $parsed['country'].$parsed['subscriber'];
    }
}
