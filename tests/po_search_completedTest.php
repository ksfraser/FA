<?php

use PHPUnit\Framework\TestCase;

class po_search_completedTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/inquiry/po_search_completed.php';
        $this->assertTrue(true);
    }
}

