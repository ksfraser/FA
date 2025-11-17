<?php

use PHPUnit\Framework\TestCase;

class work_order_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/view/work_order_view.php';
        $this->assertTrue(true);
    }
}

