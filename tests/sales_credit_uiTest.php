<?php

use PHPUnit\Framework\TestCase;

class sales_credit_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/ui/sales_credit_ui.inc';
        $this->assertTrue(true);
    }
}

