<?php

use PHPUnit\Framework\TestCase;

class usersTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/users.php';
        $this->assertTrue(true);
    }
}

