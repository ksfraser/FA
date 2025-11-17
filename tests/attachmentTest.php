<?php

use PHPUnit\Framework\TestCase;

class attachmentTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/attachment.inc';
        $this->assertTrue(true);
    }
}

