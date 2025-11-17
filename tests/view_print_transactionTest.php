<?php

use PHPUnit\Framework\TestCase;

class view_print_transactionTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/view_print_transaction.php';
        $this->assertTrue(true);
    }
}

