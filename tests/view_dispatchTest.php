<?php

use PHPUnit\Framework\TestCase;

class view_dispatchTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/view/view_dispatch.php';
        $this->assertTrue(true);
    }
}

