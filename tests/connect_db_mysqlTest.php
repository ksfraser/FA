<?php

use PHPUnit\Framework\TestCase;

class connect_db_mysqlTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/connect_db_mysql.inc';
        $this->assertTrue(true);
    }
}

