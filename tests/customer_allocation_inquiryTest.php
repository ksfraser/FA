<?php

use PHPUnit\Framework\TestCase;

class customer_allocation_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/inquiry/customer_allocation_inquiry.php';
        $this->assertTrue(true);
    }
}

