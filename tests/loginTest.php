<?php

use PHPUnit\Framework\TestCase;

class loginTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../access/login.php';
        $this->assertTrue(true);
    }
}

