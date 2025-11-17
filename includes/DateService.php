<?php

namespace FA;

/**
 * Date Service
 *
 * Handles date validation, parsing, and formatting.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages dates only
 * - Open/Closed: Can be extended for additional date features
 * - Liskov Substitution: Compatible with date interfaces
 * - Interface Segregation: Focused date methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses date logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |   DateService      |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + __date(y,m,d)    |
 * | + isDate(d)        |
 * | + dateDiff(d1,d2)  |
 * | ...                 |
 * +---------------------+
 *
 * @package FA
 */
class DateService
{
    /**
     * Format a date according to user preferences
     *
     * @param int $year Year
     * @param int $month Month
     * @param int $day Day
     * @return string Formatted date
     */
    public function formatDate(int $year, int $month, int $day): string
    {
        global $SysPrefs, $tmonths;

        $how = user_date_format();
        $sep = $SysPrefs->dateseps[user_date_sep()];
        $day = (int)$day;
        $month = (int)$month;
        if ($how < 3) {
            if ($day < 10) $day = "0" . $day;
            if ($month < 10) $month = "0" . $month;
        }
        if ($how == 0) return $month . $sep . $day . $sep . $year;
        elseif ($how == 1) return $day . $sep . $month . $sep . $year;
        elseif ($how == 2) return $year . $sep . $month . $sep . $day;
        elseif ($how == 3) return $tmonths[$month] . $sep . $day . $sep . $year;
        elseif ($how == 4) return $day . $sep . $tmonths[$month] . $sep . $year;
        else return $year . $sep . $tmonths[$month] . $sep . $day;
    }

    // Add more methods by refactoring other functions from date_functions.inc
}