<?php

use PHPUnit\Framework\TestCase;

class dimensionsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/dimensions.php';
        $this->assertTrue(true);
    }
}

