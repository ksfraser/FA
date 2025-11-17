<?php

use PHPUnit\Framework\TestCase;

class wrapper_functionsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/wrapper_functions.php';
        $this->assertTrue(true);
    }
}

