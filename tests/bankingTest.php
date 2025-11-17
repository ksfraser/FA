<?php

use PHPUnit\Framework\TestCase;

class bankingTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/banking.inc';
        $this->assertTrue(true);
    }
}

