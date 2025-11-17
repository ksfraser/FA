<?php

use PHPUnit\Framework\TestCase;

class item_adjustments_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/includes/item_adjustments_ui.inc';
        $this->assertTrue(true);
    }
}

