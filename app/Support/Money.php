<?php

namespace App\Support;

/**
 * Monetary amounts are stored as integer minor units (kobo, cents).
 */
class Money
{
    public const SYMBOLS = ['NGN' => '₦', 'USD' => '$', 'GBP' => '£'];

    public static function format(int $minor, string $currency): string
    {
        $symbol = self::SYMBOLS[$currency] ?? $currency.' ';

        return $symbol.number_format($minor / 100, 2);
    }

    public static function toMinor(string|int|float|null $major): int
    {
        if ($major === null || $major === '') {
            return 0;
        }

        return (int) round(((float) $major) * 100);
    }
}
