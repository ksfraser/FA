<?php

use PHPUnit\Framework\TestCase;

class headerTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/page/header.inc';
        $this->assertTrue(true);
    }
}

