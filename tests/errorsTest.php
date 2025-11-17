<?php

use PHPUnit\Framework\TestCase;

class errorsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/errors.inc';
        $this->assertTrue(true);
    }
}

