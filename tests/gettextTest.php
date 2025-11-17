<?php

use PHPUnit\Framework\TestCase;

class gettextTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/lang/gettext.inc';
        $this->assertTrue(true);
    }
}

