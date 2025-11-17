<?php

use PHPUnit\Framework\TestCase;

class dashboardTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/dashboard.php';
        $this->assertTrue(true);
    }
}

