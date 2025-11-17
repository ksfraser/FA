<?php

use PHPUnit\Framework\TestCase;

class connect_db_mysqliTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/connect_db_mysqli.inc';
        $this->assertTrue(true);
    }
}

