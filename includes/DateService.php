<?php

namespace FA;

use FA\Interfaces\FiscalYearRepositoryInterface;
use FA\Interfaces\CalendarConverterInterface;

/**
 * Date Service
 *
 * Handles date validation, parsing, and formatting with DI support.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages dates only
 * - Open/Closed: Can be extended for additional date features
 * - Liskov Substitution: Compatible with date interfaces
 * - Interface Segregation: Focused date methods
 * - Dependency Inversion: Depends on abstractions via DI
 *
 * DRY: Reuses date logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * |   DateService      |
 * +---------------------+
 * | - fiscalYearRepo   |
 * | - calendarConverter|
 * +---------------------+
 * | + __construct()    |
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
    private ?FiscalYearRepositoryInterface $fiscalYearRepo;
    private ?CalendarConverterInterface $calendarConverter;

    /**
     * Constructor with optional dependency injection
     *
     * @param FiscalYearRepositoryInterface|null $fiscalYearRepo Fiscal year repository
     * @param CalendarConverterInterface|null $calendarConverter Calendar converter
     */
    public function __construct(
        ?FiscalYearRepositoryInterface $fiscalYearRepo = null,
        ?CalendarConverterInterface $calendarConverter = null
    ) {
        $this->fiscalYearRepo = $fiscalYearRepo ?? new ProductionFiscalYearRepository();
        $this->calendarConverter = $calendarConverter ?? new ProductionCalendarConverter();
    }
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

        $how = \user_date_format();
        $sep = $SysPrefs->dateseps[\user_date_sep()];
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

    public function isDate(string $date): bool
    {
        if (empty($date)) return false;
        return (bool)\is_date($date);
    }

    public function today(): string
    {
        return \Today();
    }

    public function now(): string
    {
        return \Now();
    }

    public function newDocDate(?string $date = null): string
    {
        return \new_doc_date($date);
    }

    public function isDateInFiscalYear(string $date, bool $convert = false): bool
    {
        return (bool)\is_date_in_fiscalyear($date, $convert);
    }

    public function isDateClosed(string $date): bool
    {
        return (bool)\is_date_closed($date);
    }

    public function beginFiscalYear(): string
    {
        return \begin_fiscalyear();
    }

    public function endFiscalYear(): string
    {
        return \end_fiscalyear();
    }

    public function beginMonth(string $date): string
    {
        return \begin_month($date);
    }

    public static function beginMonthStatic(string $date): string
    {
        return \begin_month($date);
    }

    public function daysInMonth(int $month, int $year): int
    {
        return \days_in_month($month, $year);
    }

    public function endMonth(string $date): string
    {
        return \end_month($date);
    }

    public static function endMonthStatic(string $date): string
    {
        return \end_month($date);
    }

    public function addDays(string $date, int $days): string
    {
        return \add_days($date, $days);
    }

    public static function addDaysStatic(string $date, int $days): string
    {
        return \add_days($date, $days);
    }

    public function addMonths(string $date, int $months): string
    {
        return \add_months($date, $months);
    }

    public static function addMonthsStatic(string $date, int $months): string
    {
        return \add_months($date, $months);
    }

    public function addYears(string $date, int $years): string
    {
        return \add_years($date, $years);
    }

    public function sql2date(string $date): string
    {
        return \sql2date($date);
    }

    public function date2sql(string $date): string
    {
        return \date2sql($date);
    }

    public function sqlDateComp(string $date1, string $date2): int
    {
        return \sql_date_comp($date1, $date2);
    }

    public function dateComp(string $date1, string $date2, bool $incl_weekends = true, bool $incl_non_working = true): int
    {
        return \date_comp($date1, $date2, $incl_weekends, $incl_non_working);
    }

    public function date1GreaterDate2(string $date1, string $date2): bool
    {
        return (bool)\date1_greater_date2($date1, $date2);
    }

    public static function date1GreaterDate2Static(string $date1, string $date2): bool
    {
        return (bool)\date1_greater_date2($date1, $date2);
    }

    public function dateDiff2(string $date1, string $date2, string $period): int
    {
        return \date_diff2($date1, $date2, $period);
    }

    public function explodeDateToDmy(string $date): array
    {
        return \explode_date_to_dmy($date);
    }

    public function div(int $a, int $b): int
    {
        return \div($a, $b);
    }

    public function gregorianToJalali(int $g_y, int $g_m, int $g_d): array
    {
        return $this->calendarConverter->gregorianToJalali($g_y, $g_m, $g_d);
    }

    public function jalaliToGregorian(int $j_y, int $j_m, int $j_d): array
    {
        return $this->calendarConverter->jalaliToGregorian($j_y, $j_m, $j_d);
    }

    public function gregorianToIslamic(int $g_y, int $g_m, int $g_d): array
    {
        return $this->calendarConverter->gregorianToIslamic($g_y, $g_m, $g_d);
    }

    public function islamicToGregorian(int $i_y, int $i_m, int $i_d): array
    {
        return $this->calendarConverter->islamicToGregorian($i_y, $i_m, $i_d);
    }
    
    /**
     * Get current fiscal year
     *
     * @return array|null Fiscal year data
     */
    public function getCurrentFiscalYear(): ?array
    {
        return \get_current_fiscalyear();
    }
    
    /**
     * Static wrapper for getCurrentFiscalYear
     */
    public static function getCurrentFiscalYearStatic(): ?array
    {
        $service = new self();
        return $service->getCurrentFiscalYear();
    }
}