<?php

use PHPUnit\Framework\TestCase;

class localeTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../lang/new_language_template/locale.inc';
        $this->assertTrue(true);
    }
}

