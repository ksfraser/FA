<?php

use PHPUnit\Framework\TestCase;

class customer_allocation_mainTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/allocations/customer_allocation_main.php';
        $this->assertTrue(true);
    }
}

