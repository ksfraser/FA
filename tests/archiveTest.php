<?php

use PHPUnit\Framework\TestCase;

class archiveTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/archive.inc';
        $this->assertTrue(true);
    }
}

