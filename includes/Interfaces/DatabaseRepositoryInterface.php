<?php

namespace FA\Interfaces;

/**
 * Database Repository Interface for Data Checks
 *
 * Abstracts database operations needed for data validation checks.
 * Enables dependency injection and testing with mocks.
 *
 * @package FA\Interfaces
 */
interface DatabaseRepositoryInterface
{
    /**
     * Check if query returns empty result
     *
     * @param string $sql SQL query to check
     * @return bool True if result is not empty
     */
    public function checkEmptyResult(string $sql): bool;

    /**
     * Execute a query and return result
     *
     * @param string $sql SQL query
     * @param string $error Error message
     * @return mixed Query result
     */
    public function query(string $sql, string $error = "could not do query");

    /**
     * Fetch a row from result
     *
     * @param mixed $result Query result
     * @return array|null Row data or null
     */
    public function fetchRow($result): ?array;

    /**
     * Escape a string for SQL
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    public function escape(string $value): string;

    /**
     * Get table prefix
     *
     * @return string Table prefix
     */
    public function getTablePrefix(): string;
}