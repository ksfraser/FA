<?php

use PHPUnit\Framework\TestCase;

class password_resetTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../access/password_reset.php';
        $this->assertTrue(true);
    }
}

