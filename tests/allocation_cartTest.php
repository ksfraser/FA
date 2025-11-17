<?php

use PHPUnit\Framework\TestCase;

class allocation_cartTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/allocation_cart.inc';
        $this->assertTrue(true);
    }
}

