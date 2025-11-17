<?php

use PHPUnit\Framework\TestCase;

class courierTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/courier.php';
        $this->assertTrue(true);
    }
}

