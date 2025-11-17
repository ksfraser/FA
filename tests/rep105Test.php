<?php

use PHPUnit\Framework\TestCase;

class rep105Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep105.php';
        $this->assertTrue(true);
    }
}

