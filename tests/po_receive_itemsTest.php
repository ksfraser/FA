<?php

use PHPUnit\Framework\TestCase;

class po_receive_itemsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/po_receive_items.php';
        $this->assertTrue(true);
    }
}

