<?php

use PHPUnit\Framework\TestCase;

class wo_costs_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/view/wo_costs_view.php';
        $this->assertTrue(true);
    }
}

