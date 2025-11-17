<?php

use PHPUnit\Framework\TestCase;

class dimensions_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../dimensions/includes/dimensions_db.inc';
        $this->assertTrue(true);
    }
}

