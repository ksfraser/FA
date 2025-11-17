<?php

use PHPUnit\Framework\TestCase;

class forms_setupTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/forms_setup.php';
        $this->assertTrue(true);
    }
}

