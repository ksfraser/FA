<?php

use PHPUnit\Framework\TestCase;

class work_order_entryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/work_order_entry.php';
        $this->assertTrue(true);
    }
}

