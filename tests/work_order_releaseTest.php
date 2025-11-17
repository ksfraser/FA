<?php

use PHPUnit\Framework\TestCase;

class work_order_releaseTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/work_order_release.php';
        $this->assertTrue(true);
    }
}

