<?php
/**
 * Has Currencies Query
 *
 * Single Responsibility: Query if currencies exist
 *
 * @package FA\DataChecks\Queries
 */

namespace FA\DataChecks\Queries;

use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasCurrenciesQuery extends AbstractDatabaseExistenceQuery
{
    public function exists(): bool
    {
        return $this->executeCountQuery();
    }

    protected function getTableName(): string
    {
        return 'currencies';
    }
}
