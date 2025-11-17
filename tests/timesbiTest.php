<?php

use PHPUnit\Framework\TestCase;

class timesbiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/timesbi.php';
        $this->assertTrue(true);
    }
}

