<?php

use PHPUnit\Framework\TestCase;

class customer_deliveryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/customer_delivery.php';
        $this->assertTrue(true);
    }
}

