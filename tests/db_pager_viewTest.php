<?php

use PHPUnit\Framework\TestCase;

class db_pager_viewTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/ui/db_pager_view.inc';
        $this->assertTrue(true);
    }
}

