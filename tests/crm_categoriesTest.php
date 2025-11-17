<?php

use PHPUnit\Framework\TestCase;

class crm_categoriesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/crm_categories.php';
        $this->assertTrue(true);
    }
}

