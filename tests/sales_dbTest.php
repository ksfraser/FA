<?php

use PHPUnit\Framework\TestCase;

class sales_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/sales_db.inc';
        $this->assertTrue(true);
    }
}

