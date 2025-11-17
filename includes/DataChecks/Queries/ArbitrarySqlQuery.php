<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query for arbitrary SQL - used by checkEmptyResult()
 * 
 * Takes any SQL and checks if it returns rows
 */
class ArbitrarySqlQuery
{
    private DatabaseQueryInterface $db;
    private string $sql;

    public function __construct(DatabaseQueryInterface $db, string $sql)
    {
        $this->db = $db;
        $this->sql = $sql;
    }

    public function hasResults(): bool
    {
        return $this->db->hasRows($this->sql);
    }
}
