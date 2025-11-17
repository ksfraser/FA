<?php

use PHPUnit\Framework\TestCase;

class supp_trans_classTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../purchasing/includes/supp_trans_class.inc';
        $this->assertTrue(true);
    }
}

