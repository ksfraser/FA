<?php

use PHPUnit\Framework\TestCase;

class fixed_asset_classesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/fixed_asset_classes.php';
        $this->assertTrue(true);
    }
}

