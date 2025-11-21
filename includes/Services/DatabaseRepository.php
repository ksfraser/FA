<?php

namespace FA\Services;

use FA\Interfaces\DatabaseRepositoryInterface;

/**
 * Database Repository Implementation
 *
 * Concrete implementation of DatabaseRepositoryInterface using FrontAccounting's global functions.
 *
 * @package FA\Services
 */
class DatabaseRepository implements DatabaseRepositoryInterface
{
    /**
     * Check if query returns empty result
     *
     * @param string $sql SQL query to check
     * @return bool True if result is not empty
     */
    public function checkEmptyResult(string $sql): bool
    {
        return \check_empty_result($sql);
    }

    /**
     * Execute a query and return result
     *
     * @param string $sql SQL query
     * @param string $error Error message
     * @return mixed Query result
     */
    public function query(string $sql, string $error = "could not do query")
    {
        return \db_query($sql, $error);
    }

    /**
     * Fetch a row from result
     *
     * @param mixed $result Query result
     * @return array|null Row data or null
     */
    public function fetchRow($result): ?array
    {
        return \db_fetch_row($result);
    }

    /**
     * Escape a string for SQL
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    public function escape(string $value): string
    {
        return \db_escape($value);
    }

    /**
     * Get table prefix
     *
     * @return string Table prefix
     */
    public function getTablePrefix(): string
    {
        return \TB_PREF;
    }
}