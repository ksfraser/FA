<?php

namespace FA;

use FA\Interfaces\FiscalYearRepositoryInterface;

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
        $result = \db_query("SELECT * FROM " . TB_PREF . "fiscal_year WHERE id=" . \get_company_pref('f_year'));
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

        $begin = \sql2date($fiscalYear['begin']);
        $end = \sql2date($fiscalYear['end']);

        return (\date1_greater_date2($date, $begin) || $date == $begin) &&
               (\date1_greater_date2($end, $date) || $date == $end);
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
        return \is_closed_trans($type, \date2sql($date));
    }

    /**
     * Get fiscal year begin date
     *
     * @return string Begin date
     */
    public function getBeginFiscalYear(): string
    {
        $fiscalYear = $this->getCurrentFiscalYear();
        return $fiscalYear ? \sql2date($fiscalYear['begin']) : '';
    }

    /**
     * Get fiscal year end date
     *
     * @return string End date
     */
    public function getEndFiscalYear(): string
    {
        $fiscalYear = $this->getCurrentFiscalYear();
        return $fiscalYear ? \sql2date($fiscalYear['end']) : '';
    }
}
