<?php

use PHPUnit\Framework\TestCase;

class attachmentsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../admin/attachments.php';
        $this->assertTrue(true);
    }
}

