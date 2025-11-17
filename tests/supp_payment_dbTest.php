<?php

use PHPUnit\Framework\TestCase;

class supp_payment_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/db/supp_payment_db.inc';
        $this->assertTrue(true);
    }
}

