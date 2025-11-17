<?php

use PHPUnit\Framework\TestCase;

class shipping_companiesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/shipping_companies.php';
        $this->assertTrue(true);
    }
}

