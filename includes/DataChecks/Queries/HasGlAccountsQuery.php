<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasGlAccountsQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'chart_master'; }
}
