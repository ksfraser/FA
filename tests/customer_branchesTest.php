<?php

use PHPUnit\Framework\TestCase;

class customer_branchesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../sales/manage/customer_branches.php';
        $this->assertTrue(true);
    }
}

