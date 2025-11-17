<?php

use PHPUnit\Framework\TestCase;

class display_prefsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/display_prefs.php';
        $this->assertTrue(true);
    }
}

