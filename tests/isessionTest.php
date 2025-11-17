<?php

use PHPUnit\Framework\TestCase;

class isessionTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../install/isession.inc';
        $this->assertTrue(true);
    }
}

