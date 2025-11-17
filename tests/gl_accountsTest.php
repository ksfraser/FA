<?php

use PHPUnit\Framework\TestCase;

class gl_accountsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/gl_accounts.php';
        $this->assertTrue(true);
    }
}

