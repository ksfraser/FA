<?php

use PHPUnit\Framework\TestCase;

class recurrent_invoices_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/db/recurrent_invoices_db.inc';
        $this->assertTrue(true);
    }
}

