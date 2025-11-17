<?php

use PHPUnit\Framework\TestCase;

class items_transfer_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/includes/db/items_transfer_db.inc';
        $this->assertTrue(true);
    }
}

