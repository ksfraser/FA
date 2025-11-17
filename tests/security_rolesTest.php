<?php

use PHPUnit\Framework\TestCase;

class security_rolesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/security_roles.php';
        $this->assertTrue(true);
    }
}

