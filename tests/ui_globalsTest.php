<?php

use PHPUnit\Framework\TestCase;

class ui_globalsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/ui_globals.inc';
        $this->assertTrue(true);
    }
}

