<?php

use PHPUnit\Framework\TestCase;

class fiscalyearsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/fiscalyears.php';
        $this->assertTrue(true);
    }
}

