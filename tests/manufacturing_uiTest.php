<?php

use PHPUnit\Framework\TestCase;

class manufacturing_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/includes/manufacturing_ui.inc';
        $this->assertTrue(true);
    }
}

