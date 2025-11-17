<?php

namespace FA;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DatabaseService
{
    private Database $db;
    private array $connections;
    private int $currentCompany;

    public function __construct(Database $db, array $connections, int $currentCompany = 0)
    {
        $this->db = $db;
        $this->connections = $connections;
        $this->currentCompany = $currentCompany;
    }

    private function getPrefix(): string
    {
        return (string)($this->connections[$this->currentCompany]['tbpref'] ?? '');
    }

    private function replacePrefix(string $sql): string
    {
        return str_replace(TB_PREF, $this->getPrefix(), $sql);
    }

    public function query(string $sql, array $params = [], ?string $errMsg = null): mixed
    {
        $sql = $this->replacePrefix($sql);

        // TODO: Add logging, profiling, retries, etc.

        try {
            return $this->db->query($sql, $params);
        } catch (Exception $e) {
            if ($errMsg) {
                throw new \RuntimeException($errMsg . ': ' . $e->getMessage());
            }
            throw $e;
        }
    }

    public function execute(string $sql, array $params = [], ?string $errMsg = null): int
    {
        $sql = $this->replacePrefix($sql);

        // TODO: Add logging, etc.

        try {
            return $this->db->execute($sql, $params);
        } catch (Exception $e) {
            if ($errMsg) {
                throw new \RuntimeException($errMsg . ': ' . $e->getMessage());
            }
            throw $e;
        }
    }

    public function fetchRow(mixed $result): array|false
    {
        // Since we return arrays, adapt
        if (is_array($result) && !empty($result)) {
            return array_shift($result);
        }
        return false;
    }

    public function fetchAssoc(mixed $result): array|null
    {
        if (is_array($result) && !empty($result)) {
            return array_shift($result);
        }
        return null;
    }

    public function fetch(mixed $result): array|null
    {
        return $this->fetchAssoc($result);
    }

    public function numRows(mixed $result): int
    {
        return is_array($result) ? count($result) : 0;
    }

    public function insertId(): string
    {
        return $this->db->getLastInsertId();
    }

    // Add other methods as needed
}