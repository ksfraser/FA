<?php

namespace FA\Tests\Mocks;

use FA\Interfaces\DatabaseConnectionInterface;

/**
 * Mock Database Connection for Testing
 *
 * In-memory mock that simulates database operations without actual database access.
 *
 * @package FA\Tests\Mocks
 */
class MockDatabaseConnection implements DatabaseConnectionInterface
{
    private array $queryResults = [];
    private array $queryLog = [];
    private int $lastInsertId = 0;
    private int $affectedRows = 0;
    private string $lastError = '';
    private int $lastErrorNo = 0;
    private bool $inTransaction = false;

    /**
     * Set result for a query pattern
     *
     * @param string $pattern SQL pattern to match
     * @param array $rows Rows to return
     */
    public function setQueryResult(string $pattern, array $rows): void
    {
        $this->queryResults[$pattern] = $rows;
    }

    /**
     * Set error for next query
     *
     * @param string $message Error message
     * @param int $errno Error number
     */
    public function setError(string $message, int $errno = 1): void
    {
        $this->lastError = $message;
        $this->lastErrorNo = $errno;
    }

    /**
     * Get log of all executed queries
     *
     * @return array Query log
     */
    public function getQueryLog(): array
    {
        return $this->queryLog;
    }

    /**
     * Clear query log
     */
    public function clearQueryLog(): void
    {
        $this->queryLog = [];
    }

    public function query(string $sql, bool $showError = true)
    {
        $this->queryLog[] = $sql;

        if ($this->lastError) {
            return false;
        }

        // Find matching result
        foreach ($this->queryResults as $pattern => $rows) {
            if (stripos($sql, $pattern) !== false) {
                return ['rows' => $rows, 'index' => 0];
            }
        }

        return ['rows' => [], 'index' => 0];
    }

    public function fetch($result): ?array
    {
        if (!is_array($result) || !isset($result['rows'])) {
            return null;
        }

        if ($result['index'] >= count($result['rows'])) {
            return null;
        }

        $row = $result['rows'][$result['index']];
        $result['index']++;
        return $row;
    }

    public function fetchAll($result): array
    {
        if (!is_array($result) || !isset($result['rows'])) {
            return [];
        }

        return $result['rows'];
    }

    public function fetchRow($result): ?array
    {
        $row = $this->fetch($result);
        return $row ? array_values($row) : null;
    }

    public function escape($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_numeric($value)) {
            return (string)$value;
        }
        return "'" . addslashes((string)$value) . "'";
    }

    public function affectedRows(): int
    {
        return $this->affectedRows;
    }

    public function insertId(): int
    {
        return $this->lastInsertId;
    }

    public function setInsertId(int $id): void
    {
        $this->lastInsertId = $id;
    }

    public function setAffectedRows(int $rows): void
    {
        $this->affectedRows = $rows;
    }

    public function numRows($result): int
    {
        if (!is_array($result) || !isset($result['rows'])) {
            return 0;
        }
        return count($result['rows']);
    }

    public function freeResult($result): void
    {
        // No-op for mock
    }

    public function begin(): bool
    {
        $this->inTransaction = true;
        return true;
    }

    public function commit(): bool
    {
        $this->inTransaction = false;
        return true;
    }

    public function rollback(): bool
    {
        $this->inTransaction = false;
        return true;
    }

    public function isInTransaction(): bool
    {
        return $this->inTransaction;
    }

    public function error(): string
    {
        return $this->lastError;
    }

    public function errno(): int
    {
        return $this->lastErrorNo;
    }
}
