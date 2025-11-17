<?php

use PHPUnit\Framework\TestCase;

class credit_note_entryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/credit_note_entry.php';
        $this->assertTrue(true);
    }
}

