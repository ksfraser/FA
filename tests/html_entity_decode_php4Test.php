<?php

use PHPUnit\Framework\TestCase;

class html_entity_decode_php4Test extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/includes/html_entity_decode_php4.php';
        $this->assertTrue(true);
    }
}

