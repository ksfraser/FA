<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query to check if specific customer has branches
 * 
 * Parameterized query - takes customer ID
 */
class HasCustomerBranchesForCustomerQuery
{
    private DatabaseQueryInterface $db;
    private string $customerId;

    public function __construct(DatabaseQueryInterface $db, string $customerId)
    {
        $this->db = $db;
        $this->customerId = $customerId;
    }

    public function exists(): bool
    {
        $escapedId = $this->db->escape($this->customerId);
        $sql = "SELECT COUNT(*) FROM cust_branch WHERE debtor_no = '$escapedId'";
        $result = $this->db->query($sql);
        $row = $this->db->fetchRow($result);
        return (int)$row[0] > 0;
    }
}
