<?php

use PHPUnit\Framework\TestCase;

class exchange_ratesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        global $path_to_root;
        $path_to_root = __DIR__ . '/..';
        require_once __DIR__ . '/../gl/manage/exchange_rates.php';
        $this->assertTrue(true);
    }
}

