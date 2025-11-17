<?php

use PHPUnit\Framework\TestCase;

class change_current_user_passwordTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/change_current_user_password.php';
        $this->assertTrue(true);
    }
}

