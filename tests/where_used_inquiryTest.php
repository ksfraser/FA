<?php

use PHPUnit\Framework\TestCase;

class where_used_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/inquiry/where_used_inquiry.php';
        $this->assertTrue(true);
    }
}

