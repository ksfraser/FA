<?php

use PHPUnit\Framework\TestCase;

class item_categoriesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        global $path_to_root;
        $path_to_root = __DIR__ . '/..';
        require_once __DIR__ . '/../inventory/manage/item_categories.php';
        $this->assertTrue(true);
    }
}

