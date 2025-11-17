<?php

use PHPUnit\Framework\TestCase;

class fa_patch_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/includes/fa_patch.class.inc';
        $this->assertTrue(true);
    }
}

