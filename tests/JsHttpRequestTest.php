<?php

use PHPUnit\Framework\TestCase;

class JsHttpRequestTest extends TestCase
{
    public function testInclude()
    {
        // Test that the file can be included without errors
        require_once __DIR__ . '/../includes/JsHttpRequest.php';
        $this->assertTrue(true);
    }
}

