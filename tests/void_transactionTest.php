<?php

use PHPUnit\Framework\TestCase;

class void_transactionTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/void_transaction.php';
        $this->assertTrue(true);
    }
}

