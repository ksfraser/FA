<?php

use PHPUnit\Framework\TestCase;

class gl_trial_balanceTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/inquiry/gl_trial_balance.php';
        $this->assertTrue(true);
    }
}

