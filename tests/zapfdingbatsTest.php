<?php

use PHPUnit\Framework\TestCase;

class zapfdingbatsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/zapfdingbats.php';
        $this->assertTrue(true);
    }
}

