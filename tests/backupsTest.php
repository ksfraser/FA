<?php

use PHPUnit\Framework\TestCase;

class backupsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/backups.php';
        $this->assertTrue(true);
    }
}

