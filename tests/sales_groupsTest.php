<?php

use PHPUnit\Framework\TestCase;

class sales_groupsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/sales_groups.php';
        $this->assertTrue(true);
    }
}

