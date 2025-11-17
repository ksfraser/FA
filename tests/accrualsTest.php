<?php

use PHPUnit\Framework\TestCase;

class accrualsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/accruals.php';
        $this->assertTrue(true);
    }
}

