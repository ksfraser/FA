<?php

use PHPUnit\Framework\TestCase;

class payment_termsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/payment_terms.php';
        $this->assertTrue(true);
    }
}

