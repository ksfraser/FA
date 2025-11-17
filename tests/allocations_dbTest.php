<?php

use PHPUnit\Framework\TestCase;

class allocations_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/allocations_db.inc';
        $this->assertTrue(true);
    }
}

