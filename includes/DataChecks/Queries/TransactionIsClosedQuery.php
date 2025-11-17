<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query to check if transaction is closed
 */
class TransactionIsClosedQuery
{
    private DatabaseQueryInterface $db;
    private int $type;
    private int $typeNo;

    public function __construct(DatabaseQueryInterface $db, int $type, int $typeNo)
    {
        $this->db = $db;
        $this->type = $type;
        $this->typeNo = $typeNo;
    }

    public function isClosed(): bool
    {
        // Uses global is_closed_trans function
        return \is_closed_trans($this->type, $this->typeNo);
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getTypeNo(): int
    {
        return $this->typeNo;
    }
}
