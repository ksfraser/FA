<?php

use PHPUnit\Framework\TestCase;

class wo_production_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/view/wo_production_view.php';
        $this->assertTrue(true);
    }
}

