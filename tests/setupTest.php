<?php

use PHPUnit\Framework\TestCase;

class setupTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/setup.php';
        $this->assertTrue(true);
    }
}

