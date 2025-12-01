<?php
/**
 * FrontAccounting Database Abstraction Layer Interface
 *
 * Interface for database operations used throughout the application.
 *
 * @package FA\Database
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Database;

/**
 * Database Abstraction Layer Interface
 *
 * Provides a consistent interface for database operations.
 */
interface DBALInterface
{
    /**
     * Insert a record into a table
     *
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int The last insert ID
     */
    public function insert(string $table, array $data): int;

    /**
     * Update records in a table
     *
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where Where conditions
     * @return int Number of affected rows
     */
    public function update(string $table, array $data, array $where): int;

    /**
     * Delete records from a table
     *
     * @param string $table Table name
     * @param array $where Where conditions
     * @return int Number of affected rows
     */
    public function delete(string $table, array $where): int;

    /**
     * Fetch a single row
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null The result row or null if not found
     */
    public function fetchOne(string $sql, array $params = []): ?array;

    /**
     * Fetch all rows
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array The result rows
     */
    public function fetchAll(string $sql, array $params = []): array;

    /**
     * Execute a query and return the result
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return mixed Query result
     */
    public function query(string $sql, array $params = []): mixed;

    /**
     * Execute a statement
     *
     * @param string $sql SQL statement
     * @param array $params Statement parameters
     * @return int Number of affected rows
     */
    public function execute(string $sql, array $params = []): int;

    /**
     * Get the last insert ID
     *
     * @return string The last insert ID
     */
    public function getLastInsertId(): string;

    /**
     * Begin a transaction
     *
     * @return void
     */
    public function beginTransaction(): void;

    /**
     * Commit a transaction
     *
     * @return void
     */
    public function commit(): void;

    /**
     * Rollback a transaction
     *
     * @return void
     */
    public function rollback(): void;
}