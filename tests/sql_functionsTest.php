<?php

use PHPUnit\Framework\TestCase;

class sql_functionsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/db/sql_functions.inc';
        $this->assertTrue(true);
    }
}

