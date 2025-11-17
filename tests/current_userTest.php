<?php

use PHPUnit\Framework\TestCase;

class current_userTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/current_user.inc';
        $this->assertTrue(true);
    }
}

