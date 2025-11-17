<?php

use PHPUnit\Framework\TestCase;

class gl_bankTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/gl_bank.php';
        $this->assertTrue(true);
    }
}

