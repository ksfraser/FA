<?php

use PHPUnit\Framework\TestCase;

class symbolTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/symbol.php';
        $this->assertTrue(true);
    }
}

