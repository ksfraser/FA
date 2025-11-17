<?php

use PHPUnit\Framework\TestCase;

class customer_branches_listTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/inquiry/customer_branches_list.php';
        $this->assertTrue(true);
    }
}

