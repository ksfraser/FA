<?php

use PHPUnit\Framework\TestCase;

class work_order_costsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/work_order_costs.php';
        $this->assertTrue(true);
    }
}

