<?php

use PHPUnit\Framework\TestCase;

class sales_order_entryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        global $path_to_root;
        $path_to_root = __DIR__ . '/..';
        require_once __DIR__ . '/../sales/sales_order_entry.php';
        $this->assertTrue(true);
    }
}

