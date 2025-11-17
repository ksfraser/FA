<?php
/**
 * Abstract Database Existence Query
 *
 * Base class for all "db_has_x" queries
 * Single Responsibility: Query database for existence check
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\DatabaseQueryInterface;

abstract class AbstractDatabaseExistenceQuery
{
    protected DatabaseQueryInterface $db;

    public function __construct(DatabaseQueryInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Check if entity exists in database
     *
     * @return bool True if exists
     */
    abstract public function exists(): bool;

    /**
     * Get table name for this query
     *
     * @return string Table name
     */
    abstract protected function getTableName(): string;

    /**
     * Get WHERE clause (optional)
     *
     * @return string WHERE clause or empty string
     */
    protected function getWhereClause(): string
    {
        return '';
    }

    /**
     * Execute standard COUNT query
     *
     * @return bool True if count > 0
     */
    protected function executeCountQuery(): bool
    {
        $sql = "SELECT COUNT(*) FROM " . \TB_PREF . $this->getTableName();
        $where = $this->getWhereClause();
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        return $this->db->hasRows($sql);
    }
}
