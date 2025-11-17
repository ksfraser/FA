<?php

use PHPUnit\Framework\TestCase;

class work_order_issue_uiTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/includes/work_order_issue_ui.inc';
        $this->assertTrue(true);
    }
}

