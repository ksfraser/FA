<?php

use PHPUnit\Framework\TestCase;

class sales_pointsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/sales_points.php';
        $this->assertTrue(true);
    }
}

