<?php

use PHPUnit\Framework\TestCase;

class payment_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/payment_db.inc';
        $this->assertTrue(true);
    }
}

