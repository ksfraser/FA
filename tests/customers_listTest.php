<?php

use PHPUnit\Framework\TestCase;

class customers_listTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/inquiry/customers_list.php';
        $this->assertTrue(true);
    }
}

