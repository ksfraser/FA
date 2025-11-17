<?php

use PHPUnit\Framework\TestCase;

class po_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/po_class.inc';
        $this->assertTrue(true);
    }
}

