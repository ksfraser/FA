<?php

use PHPUnit\Framework\TestCase;

class bom_editTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/manage/bom_edit.php';
        $this->assertTrue(true);
    }
}

