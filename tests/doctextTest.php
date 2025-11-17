<?php

use PHPUnit\Framework\TestCase;

class doctextTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/doctext.inc';
        $this->assertTrue(true);
    }
}

