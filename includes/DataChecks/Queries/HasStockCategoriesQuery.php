<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasStockCategoriesQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'stock_category'; }
    protected function getWhereClause(): string { return "dflt_mb_flag!='F'"; }
}
