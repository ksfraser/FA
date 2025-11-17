<?php

use PHPUnit\Framework\TestCase;

class config_defaultTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../config.default.php';
        $this->assertTrue(true);
    }
}

