<?php

use PHPUnit\Framework\TestCase;

class credit_status_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/credit_status_db.inc';
        $this->assertTrue(true);
    }
}

