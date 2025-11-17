<?php

use PHPUnit\Framework\TestCase;

class simple_crud_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/simple_crud_class.inc';
        $this->assertTrue(true);
    }
}

