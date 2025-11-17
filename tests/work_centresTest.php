<?php

use PHPUnit\Framework\TestCase;

class work_centresTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../manufacturing/manage/work_centres.php';
        $this->assertTrue(true);
    }
}

