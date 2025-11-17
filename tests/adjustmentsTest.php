<?php

use PHPUnit\Framework\TestCase;

class adjustmentsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/adjustments.php';
        $this->assertTrue(true);
    }
}

