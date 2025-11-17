<?php

use PHPUnit\Framework\TestCase;

class rep111Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/rep111.php';
        $this->assertTrue(true);
    }
}

