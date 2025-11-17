<?php

use PHPUnit\Framework\TestCase;

class close_periodTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/close_period.php';
        $this->assertTrue(true);
    }
}

