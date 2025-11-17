<?php

use PHPUnit\Framework\TestCase;

class view_adjustmentTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/view/view_adjustment.php';
        $this->assertTrue(true);
    }
}

