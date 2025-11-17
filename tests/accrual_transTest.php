<?php

use PHPUnit\Framework\TestCase;

class accrual_transTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/view/accrual_trans.php';
        $this->assertTrue(true);
    }
}

