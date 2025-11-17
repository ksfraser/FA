<?php

use PHPUnit\Framework\TestCase;

class reportingTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/reporting.inc';
        $this->assertTrue(true);
    }
}

