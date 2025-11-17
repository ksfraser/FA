<?php

use PHPUnit\Framework\TestCase;

class barcodesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/barcodes.php';
        $this->assertTrue(true);
    }
}

