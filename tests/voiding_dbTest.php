<?php

use PHPUnit\Framework\TestCase;

class voiding_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/voiding_db.inc';
        $this->assertTrue(true);
    }
}

