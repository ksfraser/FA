<?php

use PHPUnit\Framework\TestCase;

class sales_areasTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/sales_areas.php';
        $this->assertTrue(true);
    }
}

