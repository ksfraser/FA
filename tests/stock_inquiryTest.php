<?php

use PHPUnit\Framework\TestCase;

class stock_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/inquiry/stock_inquiry.php';
        $this->assertTrue(true);
    }
}

