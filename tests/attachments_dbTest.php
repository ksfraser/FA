<?php

use PHPUnit\Framework\TestCase;

class attachments_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/db/attachments_db.inc';
        $this->assertTrue(true);
    }
}

