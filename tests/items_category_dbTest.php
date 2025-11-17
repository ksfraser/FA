<?php

use PHPUnit\Framework\TestCase;

class items_category_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/includes/db/items_category_db.inc';
        $this->assertTrue(true);
    }
}

