<?php

use PHPUnit\Framework\TestCase;

class dimension_entryTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        global $path_to_root;
        $path_to_root = __DIR__ . '/..';
        require_once __DIR__ . '/../dimensions/dimension_entry.php';
        $this->assertTrue(true);
    }
}

