<?php

use PHPUnit\Framework\TestCase;

class transactions_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/transactions_db.inc';
        $this->assertTrue(true);
    }
}

