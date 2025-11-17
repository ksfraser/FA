<?php

use PHPUnit\Framework\TestCase;

class frontaccountingTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../frontaccounting.php';
        $this->assertTrue(true);
    }
}

