<?php

use PHPUnit\Framework\TestCase;

class view_invoiceTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/view/view_invoice.php';
        $this->assertTrue(true);
    }
}

