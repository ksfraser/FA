<?php
/**
 * Production Database Query
 *
 * Production implementation of DatabaseQueryInterface using FA's legacy database functions
 * Single Responsibility: Execute database queries
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\DatabaseQueryInterface;

class ProductionDatabaseQuery implements DatabaseQueryInterface
{
    /**
     * Execute a query
     *
     * @param string $sql SQL query
     * @param string $errorMessage Error message
     * @return mixed Query result
     */
    public function query(string $sql, string $errorMessage = ''): mixed
    {
        return \db_query($sql, $errorMessage ?: "could not execute query");
    }

    /**
     * Fetch row from result
     *
     * @param mixed $result Query result
     * @return array|false
     */
    public function fetchRow($result): array|false
    {
        return \db_fetch_row($result);
    }

    /**
     * Escape value for SQL
     *
     * @param mixed $value Value to escape
     * @return string
     */
    public function escape($value): string
    {
        return \db_escape($value);
    }

    /**
     * Check if query returns rows (implements check_empty_result logic)
     *
     * @param string $sql SQL query
     * @return bool True if has rows
     */
    public function hasRows(string $sql): bool
    {
        $result = $this->query($sql, "could not do check empty query");
        $myrow = $this->fetchRow($result);
        return is_array($myrow) ? $myrow[0] > 0 : false;
    }
}
