<?php

use PHPUnit\Framework\TestCase;

class process_depreciationTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/process_depreciation.php';
        $this->assertTrue(true);
    }
}

