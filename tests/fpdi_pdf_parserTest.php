<?php

use PHPUnit\Framework\TestCase;

class fpdi_pdf_parserTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/fpdi_pdf_parser.php';
        $this->assertTrue(true);
    }
}

