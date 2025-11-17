<?php

namespace FA\Interfaces;

/**
 * Calendar Converter Interface
 *
 * Abstracts calendar conversion operations for dependency injection.
 * Supports multiple calendar systems (Gregorian, Jalali, Islamic).
 *
 * @package FA\Interfaces
 */
interface CalendarConverterInterface
{
    /**
     * Convert Gregorian date to Jalali (Persian)
     *
     * @param int $year Gregorian year
     * @param int $month Gregorian month
     * @param int $day Gregorian day
     * @return array [year, month, day] in Jalali
     */
    public function gregorianToJalali(int $year, int $month, int $day): array;

    /**
     * Convert Jalali (Persian) date to Gregorian
     *
     * @param int $year Jalali year
     * @param int $month Jalali month
     * @param int $day Jalali day
     * @return array [year, month, day] in Gregorian
     */
    public function jalaliToGregorian(int $year, int $month, int $day): array;

    /**
     * Convert Gregorian date to Islamic (Hijri)
     *
     * @param int $year Gregorian year
     * @param int $month Gregorian month
     * @param int $day Gregorian day
     * @return array [year, month, day] in Islamic
     */
    public function gregorianToIslamic(int $year, int $month, int $day): array;

    /**
     * Convert Islamic (Hijri) date to Gregorian
     *
     * @param int $year Islamic year
     * @param int $month Islamic month
     * @param int $day Islamic day
     * @return array [year, month, day] in Gregorian
     */
    public function islamicToGregorian(int $year, int $month, int $day): array;
}
