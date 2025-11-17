<?php

use PHPUnit\Framework\TestCase;

class class_reflines_crudTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/class.reflines_crud.inc';
        $this->assertTrue(true);
    }
}

