<?php
/**
 * Database Query Interface
 *
 * Abstracts database operations for dependency injection
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface DatabaseQueryInterface
{
    /**
     * Execute a query and return result
     *
     * @param string $sql SQL query
     * @param string $errorMessage Error message for debugging
     * @return mixed Query result
     */
    public function query(string $sql, string $errorMessage = ''): mixed;

    /**
     * Fetch a row from result
     *
     * @param mixed $result Query result
     * @return array|false Row data or false
     */
    public function fetchRow($result): array|false;

    /**
     * Escape value for SQL
     *
     * @param mixed $value Value to escape
     * @return string Escaped value
     */
    public function escape($value): string;

    /**
     * Check if query returns any rows
     *
     * @param string $sql SQL query
     * @return bool True if has rows
     */
    public function hasRows(string $sql): bool;
}
