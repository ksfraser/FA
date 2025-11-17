<?php

use PHPUnit\Framework\TestCase;

class supplier_invoiceTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/supplier_invoice.php';
        $this->assertTrue(true);
    }
}

