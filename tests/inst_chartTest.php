<?php

use PHPUnit\Framework\TestCase;

class inst_chartTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/inst_chart.php';
        $this->assertTrue(true);
    }
}

