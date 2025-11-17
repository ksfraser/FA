<?php

use PHPUnit\Framework\TestCase;

class date_functionsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/date_functions.inc';
        $this->assertTrue(true);
    }
}

