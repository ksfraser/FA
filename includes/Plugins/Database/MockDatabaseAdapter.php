<?php
declare(strict_types=1);

namespace Ksfraser\PluginSystem\Database;

use Ksfraser\PluginSystem\Interfaces\PluginDatabaseInterface;

/**
 * Mock Database Implementation for Testing
 *
 * Mock implementation of PluginDatabaseInterface for unit testing.
 * Stores data in memory instead of a real database.
 */
class MockDatabaseAdapter implements PluginDatabaseInterface
{
    private array $tables = [];
    private int $lastInsertId = 0;

    /**
     * Execute a query (mock implementation)
     */
    public function query(string $sql, ?string $errorMsg = null)
    {
        // Simple mock - just return true for INSERT/UPDATE/DELETE, empty array for SELECT
        if (stripos($sql, 'SELECT') === 0) {
            return $this->mockSelect($sql);
        } elseif (stripos($sql, 'INSERT') === 0) {
            return $this->mockInsert($sql);
        } elseif (stripos($sql, 'UPDATE') === 0) {
            return $this->mockUpdate($sql);
        } elseif (stripos($sql, 'DELETE') === 0) {
            return $this->mockDelete($sql);
        }

        return true;
    }

    /**
     * Fetch associative array from result (mock implementation)
     */
    public function fetchAssoc($result): ?array
    {
        if (is_array($result) && !empty($result)) {
            return array_shift($result);
        }
        return null;
    }

    /**
     * Escape a string for SQL (mock implementation)
     */
    public function escape(string $value): string
    {
        return addslashes($value);
    }

    /**
     * Get table prefix (mock implementation)
     */
    public function getTablePrefix(): string
    {
        return 'fa_';
    }

    /**
     * Get last insert ID (mock implementation)
     */
    public function insertId(): string
    {
        return (string)$this->lastInsertId;
    }

    /**
     * Mock SELECT query
     */
    private function mockSelect(string $sql): array
    {
        // Extract table name from query
        if (preg_match('/FROM\s+`?(\w+)`?/i', $sql, $matches)) {
            $table = $matches[1];
            return $this->tables[$table] ?? [];
        }
        return [];
    }

    /**
     * Mock INSERT query
     */
    private function mockInsert(string $sql): bool
    {
        // Extract table name and values
        if (preg_match('/INSERT\s+INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*\(([^)]+)\)/i', $sql, $matches)) {
            $table = $matches[1];
            $columns = array_map('trim', explode(',', $matches[2]));
            $values = array_map('trim', explode(',', $matches[3]));

            if (!isset($this->tables[$table])) {
                $this->tables[$table] = [];
            }

            $row = [];
            foreach ($columns as $i => $column) {
                $value = trim($values[$i], "'\"");
                $row[$column] = $value;
            }

            $this->lastInsertId = count($this->tables[$table]) + 1;
            $row['id'] = $this->lastInsertId;
            $this->tables[$table][] = $row;

            return true;
        }
        return false;
    }

    /**
     * Mock UPDATE query
     */
    private function mockUpdate(string $sql): bool
    {
        // Simple implementation - just return true
        return true;
    }

    /**
     * Mock DELETE query
     */
    private function mockDelete(string $sql): bool
    {
        // Simple implementation - just return true
        return true;
    }

    /**
     * Get stored data for testing
     */
    public function getTableData(string $table): array
    {
        return $this->tables[$table] ?? [];
    }

    /**
     * Clear all data
     */
    public function clearData(): void
    {
        $this->tables = [];
        $this->lastInsertId = 0;
    }
}