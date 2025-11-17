<?php

use PHPUnit\Framework\TestCase;

class gl_bank_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/ui/gl_bank_ui.inc';
        $this->assertTrue(true);
    }
}

