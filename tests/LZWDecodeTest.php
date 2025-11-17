<?php

use PHPUnit\Framework\TestCase;

class LZWDecodeTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/decoders/LZWDecode.php';
        $this->assertTrue(true);
    }
}

