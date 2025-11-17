<?php

use PHPUnit\Framework\TestCase;

class fpdiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/fpdi/fpdi.php';
        $this->assertTrue(true);
    }
}

