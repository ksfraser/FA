<?php

use PHPUnit\Framework\TestCase;

class customersTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/customers.php';
        $this->assertTrue(true);
    }
}

