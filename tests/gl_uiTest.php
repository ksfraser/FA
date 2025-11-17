<?php

use PHPUnit\Framework\TestCase;

class gl_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/gl_ui.inc';
        $this->assertTrue(true);
    }
}

