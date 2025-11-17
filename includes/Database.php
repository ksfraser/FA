<?php

namespace FA;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

class Database
{
    private Connection $connection;

    public function __construct(array $params)
    {
        $this->connection = DriverManager::getConnection($params);
    }

    public function query(string $sql, array $params = []): array
    {
        try {
            $stmt = $this->connection->executeQuery($sql, $params);
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            throw new \RuntimeException('Database query failed: ' . $e->getMessage());
        }
    }

    public function execute(string $sql, array $params = []): int
    {
        try {
            return $this->connection->executeStatement($sql, $params);
        } catch (Exception $e) {
            throw new \RuntimeException('Database execute failed: ' . $e->getMessage());
        }
    }

    public function getLastInsertId(): string
    {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction(): void
    {
        $this->connection->beginTransaction();
    }

    public function commit(): void
    {
        $this->connection->commit();
    }

    public function rollBack(): void
    {
        $this->connection->rollBack();
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}