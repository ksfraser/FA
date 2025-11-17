<?php

use PHPUnit\Framework\TestCase;

class cart_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/includes/cart_class.inc';
        $this->assertTrue(true);
    }
}

