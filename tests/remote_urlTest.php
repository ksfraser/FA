<?php

use PHPUnit\Framework\TestCase;

class remote_urlTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/remote_url.inc';
        $this->assertTrue(true);
    }
}

