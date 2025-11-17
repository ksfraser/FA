<?php

use PHPUnit\Framework\TestCase;

class suppliers_listTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/inquiry/suppliers_list.php';
        $this->assertTrue(true);
    }
}

