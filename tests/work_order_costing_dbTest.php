<?php

use PHPUnit\Framework\TestCase;

class work_order_costing_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/includes/db/work_order_costing_db.inc';
        $this->assertTrue(true);
    }
}

