<?php

use PHPUnit\Framework\TestCase;

class view_packageTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/view/view_package.php';
        $this->assertTrue(true);
    }
}

