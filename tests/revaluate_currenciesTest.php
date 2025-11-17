<?php

use PHPUnit\Framework\TestCase;

class revaluate_currenciesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/revaluate_currencies.php';
        $this->assertTrue(true);
    }
}

