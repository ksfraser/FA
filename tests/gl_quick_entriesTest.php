<?php

use PHPUnit\Framework\TestCase;

class gl_quick_entriesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/gl_quick_entries.php';
        $this->assertTrue(true);
    }
}

