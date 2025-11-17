<?php

use PHPUnit\Framework\TestCase;

class rep705Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep705.php';
        $this->assertTrue(true);
    }
}

