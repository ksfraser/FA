<?php

use PHPUnit\Framework\TestCase;

class gl_journalTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/includes/db/gl_journal.inc';
        $this->assertTrue(true);
    }
}

