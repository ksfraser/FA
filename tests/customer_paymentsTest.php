<?php

use PHPUnit\Framework\TestCase;

class customer_paymentsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/customer_payments.php';
        $this->assertTrue(true);
    }
}

