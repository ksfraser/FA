<?php

use PHPUnit\Framework\TestCase;

class fpdf_tplTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/fpdf_tpl.php';
        $this->assertTrue(true);
    }
}

