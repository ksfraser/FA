<?php

use PHPUnit\Framework\TestCase;

class system_diagnosticsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/system_diagnostics.php';
        $this->assertTrue(true);
    }
}

