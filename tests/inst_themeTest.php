<?php

use PHPUnit\Framework\TestCase;

class inst_themeTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/inst_theme.php';
        $this->assertTrue(true);
    }
}

