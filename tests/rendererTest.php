<?php

use PHPUnit\Framework\TestCase;

class rendererTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../themes/canvas/renderer.php';
        $this->assertTrue(true);
    }
}

