<?php

use PHPUnit\Framework\TestCase;

class search_work_ordersTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/search_work_orders.php';
        $this->assertTrue(true);
    }
}

