<?php

use PHPUnit\Framework\TestCase;

class data_checksTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/data_checks.inc';
        $this->assertTrue(true);
    }
}

