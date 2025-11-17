<?php

use PHPUnit\Framework\TestCase;

class timesiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/timesi.php';
        $this->assertTrue(true);
    }
}

