<?php

use PHPUnit\Framework\TestCase;

class helveticaTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../reporting/fonts/helvetica.php';
        $this->assertTrue(true);
    }
}

