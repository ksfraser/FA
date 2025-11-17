<?php

use PHPUnit\Framework\TestCase;

class sales_credit_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/sales_credit_db.inc';
        $this->assertTrue(true);
    }
}

