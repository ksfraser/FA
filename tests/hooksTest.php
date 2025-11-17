<?php

use PHPUnit\Framework\TestCase;

class hooksTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/hooks.inc';
        $this->assertTrue(true);
    }
}

