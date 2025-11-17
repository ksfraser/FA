<?php

use PHPUnit\Framework\TestCase;

class fa_classes_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../fixed_assets/includes/fa_classes_db.inc';
        $this->assertTrue(true);
    }
}

