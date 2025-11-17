<?php

use PHPUnit\Framework\TestCase;

class class_graphicTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/class.graphic.inc';
        $this->assertTrue(true);
    }
}

