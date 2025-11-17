<?php

use PHPUnit\Framework\TestCase;

class versionTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../version.php';
        $this->assertTrue(true);
    }
}

