<?php

use PHPUnit\Framework\TestCase;

class balance_sheetTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/balance_sheet.php';
        $this->assertTrue(true);
    }
}

