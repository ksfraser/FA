<?php

use PHPUnit\Framework\TestCase;

class timesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/times.php';
        $this->assertTrue(true);
    }
}

