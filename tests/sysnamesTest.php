<?php

use PHPUnit\Framework\TestCase;

class sysnamesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/sysnames.inc';
        $this->assertTrue(true);
    }
}

