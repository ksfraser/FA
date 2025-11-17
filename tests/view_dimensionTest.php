<?php

use PHPUnit\Framework\TestCase;

class view_dimensionTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../dimensions/view/view_dimension.php';
        $this->assertTrue(true);
    }
}

