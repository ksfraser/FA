<?php

use PHPUnit\Framework\TestCase;

class work_order_issueTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/work_order_issue.php';
        $this->assertTrue(true);
    }
}

