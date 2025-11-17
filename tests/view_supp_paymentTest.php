<?php

use PHPUnit\Framework\TestCase;

class view_supp_paymentTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/view/view_supp_payment.php';
        $this->assertTrue(true);
    }
}

