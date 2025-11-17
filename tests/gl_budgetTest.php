<?php

use PHPUnit\Framework\TestCase;

class gl_budgetTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/gl_budget.php';
        $this->assertTrue(true);
    }
}

