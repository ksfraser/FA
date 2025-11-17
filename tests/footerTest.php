<?php

use PHPUnit\Framework\TestCase;

class footerTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/page/footer.inc';
        $this->assertTrue(true);
    }
}

