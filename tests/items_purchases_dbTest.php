<?php

use PHPUnit\Framework\TestCase;

class items_purchases_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/includes/db/items_purchases_db.inc';
        $this->assertTrue(true);
    }
}

