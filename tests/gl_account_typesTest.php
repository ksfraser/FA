<?php

use PHPUnit\Framework\TestCase;

class gl_account_typesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/gl_account_types.php';
        $this->assertTrue(true);
    }
}

