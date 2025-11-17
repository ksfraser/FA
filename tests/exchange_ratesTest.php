<?php

use PHPUnit\Framework\TestCase;

class exchange_ratesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/exchange_rates.php';
        $this->assertTrue(true);
    }
}

