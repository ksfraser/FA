<?php

use PHPUnit\Framework\TestCase;

class class_pdfTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/class.pdf.inc';
        $this->assertTrue(true);
    }
}

