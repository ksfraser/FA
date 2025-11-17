<?php

namespace FA\Interfaces;

/**
 * Database Connection Interface
 *
 * Abstracts database operations for dependency injection.
 * Allows swapping between real database, mocks, and future implementations (PDO, ORM).
 *
 * @package FA\Interfaces
 */
interface DatabaseConnectionInterface
{
    /**
     * Execute a SQL query
     *
     * @param string $sql SQL query to execute
     * @param bool $showError Whether to display errors
     * @return mixed Query result resource or false
     */
    public function query(string $sql, bool $showError = true);

    /**
     * Fetch a single row from result
     *
     * @param mixed $result Query result resource
     * @return array|null Row data or null
     */
    public function fetch($result): ?array;

    /**
     * Fetch all rows from result
     *
     * @param mixed $result Query result resource
     * @return array Array of rows
     */
    public function fetchAll($result): array;

    /**
     * Fetch a single row as numeric array
     *
     * @param mixed $result Query result resource
     * @return array|null Row data or null
     */
    public function fetchRow($result): ?array;

    /**
     * Escape a string for SQL
     *
     * @param mixed $value Value to escape
     * @return string Escaped value
     */
    public function escape($value): string;

    /**
     * Get number of affected rows
     *
     * @return int Number of rows
     */
    public function affectedRows(): int;

    /**
     * Get last insert ID
     *
     * @return int Last insert ID
     */
    public function insertId(): int;

    /**
     * Get number of rows in result
     *
     * @param mixed $result Query result resource
     * @return int Number of rows
     */
    public function numRows($result): int;

    /**
     * Free result memory
     *
     * @param mixed $result Query result resource
     * @return void
     */
    public function freeResult($result): void;

    /**
     * Begin transaction
     *
     * @return bool Success status
     */
    public function begin(): bool;

    /**
     * Commit transaction
     *
     * @return bool Success status
     */
    public function commit(): bool;

    /**
     * Rollback transaction
     *
     * @return bool Success status
     */
    public function rollback(): bool;

    /**
     * Get last database error
     *
     * @return string Error message
     */
    public function error(): string;

    /**
     * Get last database error number
     *
     * @return int Error number
     */
    public function errno(): int;
}
