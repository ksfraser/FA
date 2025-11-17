<?php

use PHPUnit\Framework\TestCase;

class mainTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/main.inc';
        $this->assertTrue(true);
    }
}

