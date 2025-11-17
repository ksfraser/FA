<?php

use PHPUnit\Framework\TestCase;

class manufacturing_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/manufacturing_db.inc';
        $this->assertTrue(true);
    }
}

