<?php

namespace FA\Interfaces;

/**
 * Fiscal Year Repository Interface
 *
 * Abstracts access to fiscal year data for dependency injection.
 *
 * @package FA\Interfaces
 */
interface FiscalYearRepositoryInterface
{
    /**
     * Get the current fiscal year
     *
     * @return array Fiscal year record
     */
    public function getCurrentFiscalYear(): array;

    /**
     * Check if a date is within a fiscal year
     *
     * @param string $date Date to check
     * @param array|null $fiscalYear Optional fiscal year record
     * @return bool True if date is in fiscal year
     */
    public function isDateInFiscalYear(string $date, ?array $fiscalYear = null): bool;

    /**
     * Check if a date is in a closed period
     *
     * @param int $type Transaction type
     * @param string $date Date to check
     * @return bool True if date is closed
     */
    public function isDateClosed(int $type, string $date): bool;

    /**
     * Get fiscal year begin date
     *
     * @return string Begin date
     */
    public function getBeginFiscalYear(): string;

    /**
     * Get fiscal year end date
     *
     * @return string End date
     */
    public function getEndFiscalYear(): string;

    /**
     * Check if a date is within any fiscal year
     *
     * @param string $date Date to check
     * @param bool $closed Whether to include closed fiscal years (default: true)
     * @return bool True if date is in any fiscal year
     */
    public function isDateInAnyFiscalYear(string $date, bool $closed = true): bool;
