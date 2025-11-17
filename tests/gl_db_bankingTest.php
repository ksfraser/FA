<?php

use PHPUnit\Framework\TestCase;

class gl_db_bankingTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/db/gl_db_banking.inc';
        $this->assertTrue(true);
    }
}

