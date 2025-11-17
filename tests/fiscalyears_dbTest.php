<?php

use PHPUnit\Framework\TestCase;

class fiscalyears_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/fiscalyears_db.inc';
        $this->assertTrue(true);
    }
}

