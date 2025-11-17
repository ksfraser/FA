<?php

use PHPUnit\Framework\TestCase;

class customer_credit_invoiceTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/customer_credit_invoice.php';
        $this->assertTrue(true);
    }
}

