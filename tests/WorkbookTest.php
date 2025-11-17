<?php

use PHPUnit\Framework\TestCase;

class WorkbookTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/Workbook.php';
        $this->assertTrue(true);
    }
}

