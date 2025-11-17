<?php

use PHPUnit\Framework\TestCase;

class item_codesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/manage/item_codes.php';
        $this->assertTrue(true);
    }
}

