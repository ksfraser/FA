<?php

use PHPUnit\Framework\TestCase;

class ajaxTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ajax.inc';
        $this->assertTrue(true);
    }
}

