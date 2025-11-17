<?php

use PHPUnit\Framework\TestCase;

class excel_reportTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/excel_report.inc';
        $this->assertTrue(true);
    }
}

