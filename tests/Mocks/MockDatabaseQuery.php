<?php
/**
 * Mock Database Query
 *
 * Test double for DatabaseQueryInterface
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\DatabaseQueryInterface;

class MockDatabaseQuery implements DatabaseQueryInterface
{
    private array $queryResults = [];
    private array $rowCounts = [];

    /**
     * Set query result for testing
     *
     * @param string $sql SQL pattern to match
     * @param bool $hasRows Whether query should return rows
     */
    public function setQueryResult(string $sql, bool $hasRows): void
    {
        $this->rowCounts[$sql] = $hasRows ? 1 : 0;
    }

    public function query(string $sql, string $errorMessage = ''): mixed
    {
        return $sql; // Return SQL for testing
    }

    public function fetchRow($result): array|false
    {
        $count = $this->rowCounts[$result] ?? 0;
        return $count > 0 ? [$count] : false;
    }

    public function escape($value): string
    {
        return addslashes((string)$value);
    }

    public function hasRows(string $sql): bool
    {
        // Match partial SQL for testing
        foreach ($this->rowCounts as $pattern => $count) {
            if (str_contains($sql, $pattern)) {
                return $count > 0;
            }
        }
        return false;
    }

    /**
     * Clear all mock data
     */
    public function clear(): void
    {
        $this->queryResults = [];
        $this->rowCounts = [];
    }
}
