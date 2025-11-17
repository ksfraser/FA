<?php

use PHPUnit\Framework\TestCase;

class company_preferencesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/company_preferences.php';
        $this->assertTrue(true);
    }
}

