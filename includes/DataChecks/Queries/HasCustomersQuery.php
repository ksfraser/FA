<?php
/**
 * Has Customers Query
 *
 * Single Responsibility: Query if customers exist
 *
 * @package FA\DataChecks\Queries
 */

namespace FA\DataChecks\Queries;

use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasCustomersQuery extends AbstractDatabaseExistenceQuery
{
    public function exists(): bool
    {
        return $this->executeCountQuery();
    }

    protected function getTableName(): string
    {
        return 'debtors_master';
    }
}
