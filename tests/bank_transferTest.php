<?php

use PHPUnit\Framework\TestCase;

class bank_transferTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/bank_transfer.php';
        $this->assertTrue(true);
    }
}

