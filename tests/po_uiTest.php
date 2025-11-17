<?php

use PHPUnit\Framework\TestCase;

class po_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/ui/po_ui.inc';
        $this->assertTrue(true);
    }
}

