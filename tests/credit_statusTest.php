<?php

use PHPUnit\Framework\TestCase;

class credit_statusTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/credit_status.php';
        $this->assertTrue(true);
    }
}

