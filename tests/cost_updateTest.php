<?php

use PHPUnit\Framework\TestCase;

class cost_updateTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../inventory/cost_update.php';
        $this->assertTrue(true);
    }
}

