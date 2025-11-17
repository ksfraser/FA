<?php

use PHPUnit\Framework\TestCase;

class contacts_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/contacts_view.inc';
        $this->assertTrue(true);
    }
}

