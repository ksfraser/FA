<?php

use PHPUnit\Framework\TestCase;

class reports_mainTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/reports_main.php';
        $this->assertTrue(true);
    }
}

