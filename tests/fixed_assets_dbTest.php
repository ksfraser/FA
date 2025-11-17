<?php

use PHPUnit\Framework\TestCase;

class fixed_assets_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/includes/fixed_assets_db.inc';
        $this->assertTrue(true);
    }
}

