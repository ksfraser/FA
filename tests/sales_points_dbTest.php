<?php

use PHPUnit\Framework\TestCase;

class sales_points_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/sales_points_db.inc';
        $this->assertTrue(true);
    }
}

