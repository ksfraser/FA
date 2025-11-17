<?php

use PHPUnit\Framework\TestCase;

class helveticabTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/helveticab.php';
        $this->assertTrue(true);
    }
}

