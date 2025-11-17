<?php

use PHPUnit\Framework\TestCase;

class typesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/types.inc';
        $this->assertTrue(true);
    }
}

