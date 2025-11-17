<?php

use PHPUnit\Framework\TestCase;

class prn_redirectTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/prn_redirect.php';
        $this->assertTrue(true);
    }
}

