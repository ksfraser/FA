<?php

use PHPUnit\Framework\TestCase;

class alter2_3Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sql/alter2.3.php';
        $this->assertTrue(true);
    }
}

