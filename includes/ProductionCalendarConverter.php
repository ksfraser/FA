<?php

namespace FA;

use FA\Interfaces\CalendarConverterInterface;

/**
 * Production Calendar Converter
 *
 * Real implementation that performs calendar conversions.
 * Supports Gregorian, Jalali (Persian), and Islamic (Hijri) calendars.
 *
 * @package FA
 */
class ProductionCalendarConverter implements CalendarConverterInterface
{
    /**
     * Convert Gregorian date to Jalali (Persian)
     *
     * @param int $year Gregorian year
     * @param int $month Gregorian month
     * @param int $day Gregorian day
     * @return array [year, month, day] in Jalali
     */
    public function gregorianToJalali(int $year, int $month, int $day): array
    {
        return \gregorian_to_jalali($year, $month, $day);
    }

    /**
     * Convert Jalali (Persian) date to Gregorian
     *
     * @param int $year Jalali year
     * @param int $month Jalali month
     * @param int $day Jalali day
     * @return array [year, month, day] in Gregorian
     */
    public function jalaliToGregorian(int $year, int $month, int $day): array
    {
        return \jalali_to_gregorian($year, $month, $day);
    }

    /**
     * Convert Gregorian date to Islamic (Hijri)
     *
     * @param int $year Gregorian year
     * @param int $month Gregorian month
     * @param int $day Gregorian day
     * @return array [year, month, day] in Islamic
     */
    public function gregorianToIslamic(int $year, int $month, int $day): array
    {
        return \gregorian_to_islamic($year, $month, $day);
    }

    /**
     * Convert Islamic (Hijri) date to Gregorian
     *
     * @param int $year Islamic year
     * @param int $month Islamic month
     * @param int $day Islamic day
     * @return array [year, month, day] in Gregorian
     */
    public function islamicToGregorian(int $year, int $month, int $day): array
    {
        return \islamic_to_gregorian($year, $month, $day);
    }
}
