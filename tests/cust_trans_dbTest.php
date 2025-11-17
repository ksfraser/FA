<?php

use PHPUnit\Framework\TestCase;

class cust_trans_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/cust_trans_db.inc';
        $this->assertTrue(true);
    }
}

