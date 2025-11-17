<?php

use PHPUnit\Framework\TestCase;

class fpdi2tcpdf_bridgeTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/fpdi2tcpdf_bridge.php';
        $this->assertTrue(true);
    }
}

