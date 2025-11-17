<?php

use PHPUnit\Framework\TestCase;

class stock_movementsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/inquiry/stock_movements.php';
        $this->assertTrue(true);
    }
}

