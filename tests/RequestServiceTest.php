<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Services\RequestService;

class RequestServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear any existing POST data
        $_POST = [];
    }

    public function testGetPostReturnsValueWhenSet(): void
    {
        $_POST['test_key'] = 'test_value';
        
        $result = RequestService::getPostStatic('test_key');
        
        $this->assertEquals('test_value', $result);
    }

    public function testGetPostReturnsDefaultWhenNotSet(): void
    {
        $result = RequestService::getPostStatic('nonexistent', 'default_value');
        
        $this->assertEquals('default_value', $result);
    }

    public function testGetPostReturnsEmptyStringByDefault(): void
    {
        $result = RequestService::getPostStatic('nonexistent');
        
        $this->assertEquals('', $result);
    }
}
