<?php

namespace App\Support;

/**
 * Helpers for input typed on a Bangla keyboard. Many users type digits as
 * ০১২৩৪৫৬৭৮৯ – we accept them everywhere a number is expected.
 */
final class BanglaText
{
    private const BN_DIGITS = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

    public static function toAsciiDigits(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return str_replace(self::BN_DIGITS, ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'], $value);
    }

    /**
     * Normalise a Bangladeshi mobile number to the 11-digit local form
     * (01XXXXXXXXX). Accepts +880…, 880…, spaces, dashes and Bangla digits.
     * Returns the cleaned string even when invalid – validation decides.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', self::toAsciiDigits($phone));

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 2);
        } elseif (str_starts_with($digits, '88') && strlen($digits) === 13) {
            $digits = substr($digits, 2);
        }

        return $digits;
    }

    public const PHONE_REGEX = '/^01[3-9]\d{8}$/';
}
