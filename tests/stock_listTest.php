<?php

use PHPUnit\Framework\TestCase;

class stock_listTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/inquiry/stock_list.php';
        $this->assertTrue(true);
    }
}

