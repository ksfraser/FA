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

    public function testCheckValueReturnsZeroWhenNotSet(): void
    {
        $original = RequestService::checkValueStatic('nonexistent');
        $replacement = RequestService::checkValueStatic('nonexistent');
        
        $this->assertEquals(0, $original);
        $this->assertEquals(0, $replacement);
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testCheckValueReturnsOneWhenSet(): void
    {
        $_POST['checked_field'] = 'on';
        
        $original = RequestService::checkValueStatic('checked_field');
        $replacement = RequestService::checkValueStatic('checked_field');
        
        $this->assertEquals(1, $original);
        $this->assertEquals(1, $replacement);
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testCheckValueReturnsZeroWhenEmpty(): void
    {
        $_POST['empty_check'] = '';
        
        $original = RequestService::checkValueStatic('empty_check');
        $replacement = RequestService::checkValueStatic('empty_check');
        
        $this->assertEquals(0, $original);
        $this->assertEquals(0, $replacement);
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results');
    }

    public function testCheckValueHandlesArray(): void
    {
        $_POST['field1'] = 'yes';
        $_POST['field2'] = '';
        unset($_POST['field3']);
        
        $original = RequestService::checkValueStatic(['field1', 'field2', 'field3']);
        $replacement = RequestService::checkValueStatic(['field1', 'field2', 'field3']);
        
        $this->assertEquals(['field1' => 1, 'field2' => 0, 'field3' => 0], $original);
        $this->assertEquals(['field1' => 1, 'field2' => 0, 'field3' => 0], $replacement);
        $this->assertEquals($original, $replacement, 'Original and replacement must return identical results for arrays');
    }
}
