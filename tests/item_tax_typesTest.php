<?php

use PHPUnit\Framework\TestCase;

class item_tax_typesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../taxes/item_tax_types.php';
        $this->assertTrue(true);
    }
}

