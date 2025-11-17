<?php

use PHPUnit\Framework\TestCase;

class purchasing_dataTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/purchasing_data.php';
        $this->assertTrue(true);
    }
}

