<?php

use PHPUnit\Framework\TestCase;

class timeoutTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../access/timeout.php';
        $this->assertTrue(true);
    }
}

