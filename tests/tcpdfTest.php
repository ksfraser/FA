<?php

use PHPUnit\Framework\TestCase;

class tcpdfTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/tcpdf.php';
        $this->assertTrue(true);
    }
}

