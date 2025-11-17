<?php

use PHPUnit\Framework\TestCase;

class reorder_levelTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/reorder_level.php';
        $this->assertTrue(true);
    }
}

