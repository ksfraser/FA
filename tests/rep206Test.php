<?php

use PHPUnit\Framework\TestCase;

class rep206Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep206.php';
        $this->assertTrue(true);
    }
}

