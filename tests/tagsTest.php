<?php

use PHPUnit\Framework\TestCase;

class tagsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/tags.php';
        $this->assertTrue(true);
    }
}

