<?php

use PHPUnit\Framework\TestCase;

class header2Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/header2.inc';
        $this->assertTrue(true);
    }
}

