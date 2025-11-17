<?php

use PHPUnit\Framework\TestCase;

class po_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/db/po_db.inc';
        $this->assertTrue(true);
    }
}

