<?php

use PHPUnit\Framework\TestCase;

class gl_journal_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/ui/gl_journal_ui.inc';
        $this->assertTrue(true);
    }
}

