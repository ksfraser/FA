<?php

use PHPUnit\Framework\TestCase;

class sales_kitsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/manage/sales_kits.php';
        $this->assertTrue(true);
    }
}

