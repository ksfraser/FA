<?php

use PHPUnit\Framework\TestCase;

class sales_orders_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/inquiry/sales_orders_view.php';
        $this->assertTrue(true);
    }
}

