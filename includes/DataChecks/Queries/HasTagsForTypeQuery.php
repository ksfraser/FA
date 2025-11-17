<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query to check if system has tags for specific type
 * 
 * Parameterized query - takes tag type
 */
class HasTagsForTypeQuery
{
    private DatabaseQueryInterface $db;
    private ?int $type;

    public function __construct(DatabaseQueryInterface $db, ?int $type = null)
    {
        $this->db = $db;
        $this->type = $type;
    }

    public function exists(): bool
    {
        $sql = "SELECT COUNT(*) FROM tags";
        if ($this->type !== null) {
            $sql .= " WHERE type = " . (int)$this->type;
        }
        $result = $this->db->query($sql);
        $row = $this->db->fetchRow($result);
        return (int)$row[0] > 0;
    }
}
