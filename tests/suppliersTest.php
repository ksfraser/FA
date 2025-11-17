<?php

use PHPUnit\Framework\TestCase;

class suppliersTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/suppliers.php';
        $this->assertTrue(true);
    }
}

