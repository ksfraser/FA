<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\FiscalYearRepositoryInterface;

/**
 * Mock Fiscal Year Repository for Testing
 *
 * @package FA\Tests\Mocks
 */
class MockFiscalYearRepository implements FiscalYearRepositoryInterface
{
    private array $fiscalYear;
    private array $closedDates = [];

    public function __construct(array $fiscalYear = [])
    {
        $this->fiscalYear = $fiscalYear ?: [
            'id' => 1,
            'begin' => '2024-01-01',
            'end' => '2024-12-31',
            'closed' => 0
        ];
    }

    public function setFiscalYear(array $fiscalYear): void
    {
        $this->fiscalYear = $fiscalYear;
    }

    public function setClosedDate(int $type, string $date): void
    {
        $this->closedDates[$type][$date] = true;
    }

    public function getCurrentFiscalYear(): array
    {
        return $this->fiscalYear;
    }

    public function isDateInFiscalYear(string $date, ?array $fiscalYear = null): bool
    {
        if ($fiscalYear === null) {
            $fiscalYear = $this->fiscalYear;
        }

        if (empty($fiscalYear)) {
            return true;
        }

        $begin = $fiscalYear['begin'];
        $end = $fiscalYear['end'];

        return ($date >= $begin && $date <= $end);
    }

    public function isDateClosed(int $type, string $date): bool
    {
        return isset($this->closedDates[$type][$date]);
    }

    public function getBeginFiscalYear(): string
    {
        return $this->fiscalYear['begin'] ?? '';
    }

    public function getEndFiscalYear(): string
    {
        return $this->fiscalYear['end'] ?? '';
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
        if (empty($this->fiscalYear)) {
            return false;
        }

        if (!$closed && $this->fiscalYear['closed']) {
            return false;
        }

        return ($date >= $this->fiscalYear['begin'] && $date <= $this->fiscalYear['end']);
    }
