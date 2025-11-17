<?php

use PHPUnit\Framework\TestCase;

class view_poTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/view/view_po.php';
        $this->assertTrue(true);
    }
}

