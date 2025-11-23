<?php

namespace FA;

use FA\Interfaces\FiscalYearRepositoryInterface;
use FA\Services\CompanyPrefsService;

/**
 * Production Fiscal Year Repository
 *
 * Real implementation that accesses the database for fiscal year data.
 *
 * @package FA
 */
class ProductionFiscalYearRepository implements FiscalYearRepositoryInterface
{
    /**
     * Get the current fiscal year
     *
     * @return array Fiscal year record
     */
    public function getCurrentFiscalYear(): array
    {
        $result = \db_query("SELECT * FROM " . TB_PREF . "fiscal_year WHERE id=" . CompanyPrefsService::getCompanyPref('f_year'));
        return \db_fetch($result) ?: [];
    }

    /**
     * Check if a date is within a fiscal year
     *
     * @param string $date Date to check
     * @param array|null $fiscalYear Optional fiscal year record
     * @return bool True if date is in fiscal year
     */
    public function isDateInFiscalYear(string $date, ?array $fiscalYear = null): bool
    {
        if ($fiscalYear === null) {
            $fiscalYear = $this->getCurrentFiscalYear();
        }

        if (empty($fiscalYear)) {
            return true;
        }

        $begin = \DateService::sql2dateStatic($fiscalYear['begin']);
        $end = \DateService::sql2dateStatic($fiscalYear['end']);

        return (\DateService::date1GreaterDate2Static($date, $begin) || $date == $begin) &&
               (\DateService::date1GreaterDate2Static($end, $date) || $date == $end);
    }

    /**
     * Check if a date is in a closed period
     *
     * @param int $type Transaction type
     * @param string $date Date to check
     * @return bool True if date is closed
     */
    public function isDateClosed(int $type, string $date): bool
    {
        return \is_closed_trans($type, \DateService::date2sqlStatic($date));
    }

    /**
     * Get fiscal year begin date
     *
     * @return string Begin date
     */
    public function getBeginFiscalYear(): string
    {
        $fiscalYear = $this->getCurrentFiscalYear();
        return $fiscalYear ? \DateService::sql2dateStatic($fiscalYear['begin']) : '';
    }

    /**
     * Get fiscal year end date
     *
     * @return string End date
     */
    public function getEndFiscalYear(): string
    {
        $fiscalYear = $this->getCurrentFiscalYear();
        return $fiscalYear ? \DateService::sql2dateStatic($fiscalYear['end']) : '';
    }

    /**
     * Check if a date is within any fiscal year
     *
     * @param string $date Date to check
     * @param bool $closed Whether to include closed fiscal years (default: true)
     * @return bool True if date is in any fiscal year
     */
    public function isDateInAnyFiscalYear(string $date, bool $closed = true): bool
    {
        $sqlDate = \FA\DateService::date2sqlStatic($date);
        $sql = "SELECT * FROM " . TB_PREF . "fiscal_year WHERE '$sqlDate' >= begin AND '$sqlDate' <= end";
        if (!$closed) {
            $sql .= " AND closed=0";
        }
        $result = \db_query($sql, "could not check fiscal years");
        return \db_fetch($result) !== false;
    }
}
