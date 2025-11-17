<?php

use PHPUnit\Framework\TestCase;

class inventory_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/inventory_db.inc';
        $this->assertTrue(true);
    }
}

