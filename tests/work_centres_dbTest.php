<?php

use PHPUnit\Framework\TestCase;

class work_centres_dbTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/includes/db/work_centres_db.inc';
        $this->assertTrue(true);
    }
}

