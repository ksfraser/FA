<?php

use PHPUnit\Framework\TestCase;

class printers_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/printers_db.inc';
        $this->assertTrue(true);
    }
}

