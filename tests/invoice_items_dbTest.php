<?php

use PHPUnit\Framework\TestCase;

class invoice_items_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/db/invoice_items_db.inc';
        $this->assertTrue(true);
    }
}

