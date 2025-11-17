<?php

namespace FA\DataChecks\Queries;

use FA\Contracts\DatabaseQueryInterface;

/**
 * Query to check if user can edit transaction
 */
class TransactionIsEditableQuery
{
    private DatabaseQueryInterface $db;
    private int $transType;
    private int $transNo;

    public function __construct(DatabaseQueryInterface $db, int $transType, int $transNo)
    {
        $this->db = $db;
        $this->transType = $transType;
        $this->transNo = $transNo;
    }

    public function canEdit(): bool
    {
        // Check if user can edit other users' transactions
        if (!$_SESSION['wa_current_user']->can_access('SA_EDITOTHERSTRANS')) {
            $audit = \get_audit_trail_last($this->transType, $this->transNo);
            if ($_SESSION['wa_current_user']->user != $audit['user']) {
                return false;
            }
        }
        return true;
    }

    public function getTransType(): int
    {
        return $this->transType;
    }

    public function getTransNo(): int
    {
        return $this->transNo;
    }
}
