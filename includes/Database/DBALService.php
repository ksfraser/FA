<?php
/**
 * FrontAccounting Database Abstraction Layer Service
 *
 * Concrete implementation of DBALInterface using Doctrine DBAL.
 *
 * @package FA\Database
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Database;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Database Abstraction Layer Service
 *
 * Provides database operations using Doctrine DBAL.
 */
class DBALService implements DBALInterface
{
    private Connection $connection;
    private string $tablePrefix;

    public function __construct(Connection $connection, string $tablePrefix = '')
    {
        $this->connection = $connection;
        $this->tablePrefix = $tablePrefix;
    }

    /**
     * Apply table prefix to table name
     *
     * @param string $table
     * @return string
     */
    private function applyPrefix(string $table): string
    {
        if (empty($this->tablePrefix)) {
            return $table;
        }

        // If table already has prefix, don't add it again
        if (strpos($table, $this->tablePrefix) === 0) {
            return $table;
        }

        return $this->tablePrefix . $table;
    }

    /**
     * {@inheritdoc}
     */
    public function insert(string $table, array $data): int
    {
        $table = $this->applyPrefix($table);

        try {
            $this->connection->insert($table, $data);
            return (int)$this->connection->lastInsertId();
        } catch (Exception $e) {
            throw new \RuntimeException('Database insert failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $table, array $data, array $where): int
    {
        $table = $this->applyPrefix($table);

        try {
            return $this->connection->update($table, $data, $where);
        } catch (Exception $e) {
            throw new \RuntimeException('Database update failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $table, array $where): int
    {
        $table = $this->applyPrefix($table);

        try {
            return $this->connection->delete($table, $where);
        } catch (Exception $e) {
            throw new \RuntimeException('Database delete failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        try {
            $stmt = $this->connection->executeQuery($sql, $params);
            $result = $stmt->fetchAssociative();

            return $result ?: null;
        } catch (Exception $e) {
            throw new \RuntimeException('Database query failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->executeQuery($sql, $params);
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new \RuntimeException('Database query failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function query(string $sql, array $params = []): mixed
    {
        try {
            $stmt = $this->connection->executeQuery($sql, $params);
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new \RuntimeException('Database query failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $sql, array $params = []): int
    {
        try {
            return $this->connection->executeStatement($sql, $params);
        } catch (Exception $e) {
            throw new \RuntimeException('Database execute failed: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    /**
     * {@inheritdoc}
     */
    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): void
    {
        $this->connection->commit();
    }

    /**
     * {@inheritdoc}
     */
    public function rollback(): void
    {
        $this->connection->rollBack();
    }
}