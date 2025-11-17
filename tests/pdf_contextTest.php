<?php

use PHPUnit\Framework\TestCase;

class pdf_contextTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/pdf_context.php';
        $this->assertTrue(true);
    }
}

