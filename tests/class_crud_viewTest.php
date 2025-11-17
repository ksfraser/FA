<?php

use PHPUnit\Framework\TestCase;

class class_crud_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/class.crud_view.inc';
        $this->assertTrue(true);
    }
}

