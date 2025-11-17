<?php

use PHPUnit\Framework\TestCase;

class profit_lossTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/profit_loss.php';
        $this->assertTrue(true);
    }
}

