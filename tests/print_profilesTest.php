<?php

use PHPUnit\Framework\TestCase;

class print_profilesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/print_profiles.php';
        $this->assertTrue(true);
    }
}

