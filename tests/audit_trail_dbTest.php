<?php

use PHPUnit\Framework\TestCase;

class audit_trail_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/audit_trail_db.inc';
        $this->assertTrue(true);
    }
}

