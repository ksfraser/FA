<?php

use PHPUnit\Framework\TestCase;

class helveticabiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/helveticabi.php';
        $this->assertTrue(true);
    }
}

