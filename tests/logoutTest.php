<?php

use PHPUnit\Framework\TestCase;

class logoutTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../access/logout.php';
        $this->assertTrue(true);
    }
}

