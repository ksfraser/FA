<?php

use PHPUnit\Framework\TestCase;

class manufacturingTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/manufacturing.php';
        $this->assertTrue(true);
    }
}

