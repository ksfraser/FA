<?php

use PHPUnit\Framework\TestCase;

class userprefsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/prefs/userprefs.inc';
        $this->assertTrue(true);
    }
}

