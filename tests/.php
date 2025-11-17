<?php

use PHPUnit\Framework\TestCase;

class  extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../access/index.php';
        $this->assertTrue(true);
    }
}

