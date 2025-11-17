<?php

use PHPUnit\Framework\TestCase;

class journal_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/journal_inquiry.php';
        $this->assertTrue(true);
    }
}

