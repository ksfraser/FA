<?php

use PHPUnit\Framework\TestCase;

class bank_transfer_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/view/bank_transfer_view.php';
        $this->assertTrue(true);
    }
}

