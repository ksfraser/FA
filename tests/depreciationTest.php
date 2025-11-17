<?php

use PHPUnit\Framework\TestCase;

class depreciationTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/includes/depreciation.inc';
        $this->assertTrue(true);
    }
}

