<?php

use PHPUnit\Framework\TestCase;

class alter2_1Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sql/alter2.1.php';
        $this->assertTrue(true);
    }
}

