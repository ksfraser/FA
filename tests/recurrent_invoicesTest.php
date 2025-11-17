<?php

use PHPUnit\Framework\TestCase;

class recurrent_invoicesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/recurrent_invoices.php';
        $this->assertTrue(true);
    }
}

