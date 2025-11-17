<?php

use PHPUnit\Framework\TestCase;

class tax_groupsTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../taxes/tax_groups.php';
        $this->assertTrue(true);
    }
}

