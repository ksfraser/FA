<?php

use PHPUnit\Framework\TestCase;

class uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui.inc';
        $this->assertTrue(true);
    }
}

