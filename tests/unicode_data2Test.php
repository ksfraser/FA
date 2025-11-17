<?php

use PHPUnit\Framework\TestCase;

class unicode_data2Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/unicode_data2.php';
        $this->assertTrue(true);
    }
}

