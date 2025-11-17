<?php

use PHPUnit\Framework\TestCase;

class printersTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/printers.php';
        $this->assertTrue(true);
    }
}

