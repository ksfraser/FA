<?php

use PHPUnit\Framework\TestCase;

class company_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/company_db.inc';
        $this->assertTrue(true);
    }
}

