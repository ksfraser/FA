<?php

use PHPUnit\Framework\TestCase;

class pdf_reportTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/pdf_report.inc';
        $this->assertTrue(true);
    }
}

