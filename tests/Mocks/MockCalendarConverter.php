<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\CalendarConverterInterface;

/**
 * Mock Calendar Converter for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockCalendarConverter implements CalendarConverterInterface
{
    public function gregorianToJalali(int $year, int $month, int $day): array
    {
        // Simple mock: just add 621 years for Persian calendar
        return [$year - 621, $month, $day];
    }

    public function jalaliToGregorian(int $year, int $month, int $day): array
    {
        // Simple mock: subtract 621 years
        return [$year + 621, $month, $day];
    }

    public function gregorianToIslamic(int $year, int $month, int $day): array
    {
        // Simple mock: add 579 years for Islamic calendar
        return [$year - 579, $month, $day];
    }

    public function islamicToGregorian(int $year, int $month, int $day): array
    {
        // Simple mock: subtract 579 years
        return [$year + 579, $month, $day];
    }
}
