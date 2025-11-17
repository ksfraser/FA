<?php

use PHPUnit\Framework\TestCase;

class generalledgerTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../applications/generalledger.php';
        $this->assertTrue(true);
    }
}

