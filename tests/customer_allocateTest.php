<?php

use PHPUnit\Framework\TestCase;

class customer_allocateTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/allocations/customer_allocate.php';
        $this->assertTrue(true);
    }
}

