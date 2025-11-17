<?php

use PHPUnit\Framework\TestCase;

class reports_classesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/reports_classes.inc';
        $this->assertTrue(true);
    }
}

