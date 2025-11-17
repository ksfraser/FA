<?php

use PHPUnit\Framework\TestCase;

class sales_order_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/ui/sales_order_ui.inc';
        $this->assertTrue(true);
    }
}

