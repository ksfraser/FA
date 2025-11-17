<?php

use PHPUnit\Framework\TestCase;

class grn_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/ui/grn_ui.inc';
        $this->assertTrue(true);
    }
}

