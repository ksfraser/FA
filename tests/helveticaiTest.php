<?php

use PHPUnit\Framework\TestCase;

class helveticaiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/helveticai.php';
        $this->assertTrue(true);
    }
}

