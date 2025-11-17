<?php

use PHPUnit\Framework\TestCase;

class ui_msgsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/ui_msgs.inc';
        $this->assertTrue(true);
    }
}

