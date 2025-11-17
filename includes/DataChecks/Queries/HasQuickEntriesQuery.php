<?php
namespace FA\DataChecks\Queries;
use FA\DataChecks\AbstractDatabaseExistenceQuery;

class HasQuickEntriesQuery extends AbstractDatabaseExistenceQuery {
    public function exists(): bool { return $this->executeCountQuery(); }
    protected function getTableName(): string { return 'quick_entries'; }
}
