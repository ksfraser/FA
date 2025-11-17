<?php

use PHPUnit\Framework\TestCase;

class rep309Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep309.php';
        $this->assertTrue(true);
    }
}

