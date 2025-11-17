<?php

use PHPUnit\Framework\TestCase;

class pricesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/prices.php';
        $this->assertTrue(true);
    }
}

