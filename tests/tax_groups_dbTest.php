<?php

use PHPUnit\Framework\TestCase;

class tax_groups_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../taxes/db/tax_groups_db.inc';
        $this->assertTrue(true);
    }
}

