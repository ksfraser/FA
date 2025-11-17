<?php

use PHPUnit\Framework\TestCase;

class search_dimensionsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../dimensions/inquiry/search_dimensions.php';
        $this->assertTrue(true);
    }
}

