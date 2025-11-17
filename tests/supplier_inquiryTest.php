<?php

use PHPUnit\Framework\TestCase;

class supplier_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/inquiry/supplier_inquiry.php';
        $this->assertTrue(true);
    }
}

