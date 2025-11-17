<?php

use PHPUnit\Framework\TestCase;

class sysprefsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/prefs/sysprefs.inc';
        $this->assertTrue(true);
    }
}

