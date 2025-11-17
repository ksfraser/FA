<?php

use PHPUnit\Framework\TestCase;

class tax_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/tax_inquiry.php';
        $this->assertTrue(true);
    }
}

