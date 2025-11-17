<?php

use PHPUnit\Framework\TestCase;

class fixed_assetsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/fixed_assets.php';
        $this->assertTrue(true);
    }
}

