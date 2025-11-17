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

    public function testInputNumReturnsDefaultWhenNotSet(): void
    {
        $result = RequestService::inputNumStatic('nonexistent', 42.5);
        
        $this->assertEquals(42.5, $result);
    }

    public function testInputNumReturnsDefaultWhenEmpty(): void
    {
        $_POST['empty_field'] = '';
        
        $result = RequestService::inputNumStatic('empty_field', 10);
        
        $this->assertEquals(10, $result);
    }
}
