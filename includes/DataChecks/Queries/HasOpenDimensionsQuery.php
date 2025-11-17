<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasOpenDimensionsQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'dimensions'; }
    protected function getWhereClause(): string { return 'closed=0'; }
}
