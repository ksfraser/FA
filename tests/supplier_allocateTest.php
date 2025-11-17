<?php

use PHPUnit\Framework\TestCase;

class supplier_allocateTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/allocations/supplier_allocate.php';
        $this->assertTrue(true);
    }
}

