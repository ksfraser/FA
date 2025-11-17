<?php

use PHPUnit\Framework\TestCase;

class gl_db_accountsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/db/gl_db_accounts.inc';
        $this->assertTrue(true);
    }
}

