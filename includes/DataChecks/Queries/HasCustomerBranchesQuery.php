<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasCustomerBranchesQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'cust_branch'; }
    protected function getWhereClause(): string { return '!inactive'; }
}
