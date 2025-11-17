<?php

use PHPUnit\Framework\TestCase;

class bank_account_reconcileTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/bank_account_reconcile.php';
        $this->assertTrue(true);
    }
}

