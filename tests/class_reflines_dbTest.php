<?php

use PHPUnit\Framework\TestCase;

class class_reflines_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/class.reflines_db.inc';
        $this->assertTrue(true);
    }
}

