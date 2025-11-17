<?php

use PHPUnit\Framework\TestCase;

class ui_listsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/ui_lists.inc';
        $this->assertTrue(true);
    }
}

