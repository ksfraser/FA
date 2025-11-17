<?php

use PHPUnit\Framework\TestCase;

class rep601Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep601.php';
        $this->assertTrue(true);
    }
}

