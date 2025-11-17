<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasManufacturableItemsQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'stock_master'; }
    protected function getWhereClause(): string { return "mb_flag='M'"; }
}
