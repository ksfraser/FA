<?php

use PHPUnit\Framework\TestCase;

class stock_statusTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/inquiry/stock_status.php';
        $this->assertTrue(true);
    }
}

