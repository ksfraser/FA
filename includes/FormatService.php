<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * Format Service
 * 
 * Handles number and currency formatting with user preferences.
 * Replaces legacy FormatService::numberFormat2() and FormatService::priceFormat() functions.
 * 
 * Uses UserPrefsCache to avoid repeated session lookups (191+ price_dec calls,
 * 378+ tho_sep calls, 378+ dec_sep calls reduced to 1 each per request).
 */
class FormatService
{
    /**
     * Format number according to user preferences
     * 
     * Formats a number with thousands separator and decimal separator
     * according to current user's locale settings.
     * 
     * @param float|int $number The number to format
     * @param int|string $decimals Number of decimal places, or 'max' for automatic
     * @return string Formatted number string
     */
    public static function numberFormat2(float|int $number, int|string $decimals = 0): string
    {
        global $SysPrefs;
        $tsep = $SysPrefs->thoseps[UserPrefsCache::getThousandsSeparator()];
        $dsep = $SysPrefs->decseps[UserPrefsCache::getDecimalSeparator()];

        $number = (float)$number;
        if($decimals === 'max') {
            $dec = 15 - floor(log10(abs($number)));
        } else {
            $delta = ($number < 0 ? -.0000000001 : .0000000001);
            $number += $delta;
            $dec = $decimals;
        }

        $num = number_format($number, intval($dec), $dsep, $tsep);

        return $decimals === 'max' ? rtrim($num, '0') : $num;
    }
    
    /**
     * Format price with user's decimal precision
     * 
     * Formats a price value using the user's configured price decimal places.
     * This is a convenience wrapper around numberFormat2().
     * 
     * @param float|int $number The price to format
     * @return string Formatted price string
     */
    public static function priceFormat(float|int $number): string
    {
        return self::numberFormat2($number, UserPrefsCache::getPriceDecimals());
    }
}
