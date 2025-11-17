<?php

use PHPUnit\Framework\TestCase;

class view_sales_orderTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/view/view_sales_order.php';
        $this->assertTrue(true);
    }
}

