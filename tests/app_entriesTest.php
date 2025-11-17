<?php

use PHPUnit\Framework\TestCase;

class app_entriesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/app_entries.inc';
        $this->assertTrue(true);
    }
}

