<?php

use PHPUnit\Framework\TestCase;

class referencesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/references.inc';
        $this->assertTrue(true);
    }
}

