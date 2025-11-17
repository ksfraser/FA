<?php

use PHPUnit\Framework\TestCase;

class item_unitsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/manage/item_units.php';
        $this->assertTrue(true);
    }
}

