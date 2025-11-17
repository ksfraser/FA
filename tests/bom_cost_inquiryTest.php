<?php

use PHPUnit\Framework\TestCase;

class bom_cost_inquiryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/inquiry/bom_cost_inquiry.php';
        $this->assertTrue(true);
    }
}

