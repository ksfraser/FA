<?php

use PHPUnit\Framework\TestCase;

class gl_account_classesTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../gl/manage/gl_account_classes.php';
        $this->assertTrue(true);
    }
}

