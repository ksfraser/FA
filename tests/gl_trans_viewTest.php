<?php

use PHPUnit\Framework\TestCase;

class gl_trans_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/view/gl_trans_view.php';
        $this->assertTrue(true);
    }
}

