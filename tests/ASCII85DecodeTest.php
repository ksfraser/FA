<?php

use PHPUnit\Framework\TestCase;

class ASCII85DecodeTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/decoders/ASCII85Decode.php';
        $this->assertTrue(true);
    }
}

