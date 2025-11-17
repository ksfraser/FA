<?php

use PHPUnit\Framework\TestCase;

class timesbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/timesb.php';
        $this->assertTrue(true);
    }
}

