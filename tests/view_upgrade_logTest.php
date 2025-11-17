<?php

use PHPUnit\Framework\TestCase;

class view_upgrade_logTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/view/view_upgrade_log.php';
        $this->assertTrue(true);
    }
}

