<?php

use PHPUnit\Framework\TestCase;

class pdf_parserTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/pdf_parser.php';
        $this->assertTrue(true);
    }
}

