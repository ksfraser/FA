<?php

namespace FA;

use FA\Interfaces\DatabaseConnectionInterface;

/**
 * Production Database Connection
 *
 * Real implementation that wraps legacy database functions.
 * Provides a bridge between new OOP code and legacy procedural database layer.
 *
 * @package FA
 */
class ProductionDatabaseConnection implements DatabaseConnectionInterface
{
    /**
     * Execute a SQL query
     *
     * @param string $sql SQL query to execute
     * @param bool $showError Whether to display errors
     * @return mixed Query result resource or false
     */
    public function query(string $sql, bool $showError = true)
    {
        return \db_query($sql, $showError ? "Error executing query" : null);
    }

    /**
     * Fetch a single row from result
     *
     * @param mixed $result Query result resource
     * @return array|null Row data or null
     */
    public function fetch($result): ?array
    {
        $row = \db_fetch($result);
        return $row ?: null;
    }

    /**
     * Fetch all rows from result
     *
     * @param mixed $result Query result resource
     * @return array Array of rows
     */
    public function fetchAll($result): array
    {
        $rows = [];
        while ($row = \db_fetch($result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    /**
     * Fetch a single row as numeric array
     *
     * @param mixed $result Query result resource
     * @return array|null Row data or null
     */
    public function fetchRow($result): ?array
    {
        $row = \db_fetch_row($result);
        return $row ?: null;
    }

    /**
     * Escape a string for SQL
     *
     * @param mixed $value Value to escape
     * @return string Escaped value
     */
    public function escape($value): string
    {
        return \db_escape($value);
    }

    /**
     * Get number of affected rows
     *
     * @return int Number of rows
     */
    public function affectedRows(): int
    {
        return \db_num_affected_rows();
    }

    /**
     * Get last insert ID
     *
     * @return int Last insert ID
     */
    public function insertId(): int
    {
        return \db_insert_id();
    }

    /**
     * Get number of rows in result
     *
     * @param mixed $result Query result resource
     * @return int Number of rows
     */
    public function numRows($result): int
    {
        return \db_num_rows($result);
    }

    /**
     * Free result memory
     *
     * @param mixed $result Query result resource
     * @return void
     */
    public function freeResult($result): void
    {
        if ($result) {
            \db_free_result($result);
        }
    }

    /**
     * Begin transaction
     *
     * @return bool Success status
     */
    public function begin(): bool
    {
        return (bool)\begin_transaction();
    }

    /**
     * Commit transaction
     *
     * @return bool Success status
     */
    public function commit(): bool
    {
        return (bool)\commit_transaction();
    }

    /**
     * Rollback transaction
     *
     * @return bool Success status
     */
    public function rollback(): bool
    {
        return (bool)\cancel_transaction();
    }

    /**
     * Get last database error
     *
     * @return string Error message
     */
    public function error(): string
    {
        return \db_error_msg();
    }

    /**
     * Get last database error number
     *
     * @return int Error number
     */
    public function errno(): int
    {
        return \db_error_no();
    }
}
