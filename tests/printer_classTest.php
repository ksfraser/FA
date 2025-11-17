<?php

use PHPUnit\Framework\TestCase;

class printer_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/printer_class.inc';
        $this->assertTrue(true);
    }
}

