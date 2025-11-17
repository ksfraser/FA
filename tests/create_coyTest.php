<?php

use PHPUnit\Framework\TestCase;

class create_coyTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/create_coy.php';
        $this->assertTrue(true);
    }
}

