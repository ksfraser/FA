<?php

use PHPUnit\Framework\TestCase;

class gl_account_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/gl_account_inquiry.php';
        $this->assertTrue(true);
    }
}

