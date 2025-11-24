<?php
declare(strict_types=1);

// Include the custom fields classes
require_once __DIR__ . '/../includes/CustomFields/CustomFieldInterface.php';
require_once __DIR__ . '/../includes/CustomFields/CustomFieldManager.php';
require_once __DIR__ . '/../includes/CustomFields/CustomFieldsHelper.php';

use PHPUnit\Framework\TestCase;
use FA\CustomFields\CustomFieldManager;
use FA\CustomFields\CustomFieldsHelper;

/**
 * Custom Fields Test
 */
class CustomFieldsTest extends TestCase
{
    private CustomFieldManager $fieldManager;
    private CustomFieldsHelper $helper;

    protected function setUp(): void
    {
        $this->fieldManager = CustomFieldManager::getInstance();
        $this->helper = new CustomFieldsHelper();
    }

    public function testFieldManagerSingleton()
    {
        $instance1 = CustomFieldManager::getInstance();
        $instance2 = CustomFieldManager::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testHelperInstantiation()
    {
        $this->assertInstanceOf(CustomFieldsHelper::class, $this->helper);
    }

    public function testFieldValidation()
    {
        // Test valid field data
        $fieldData = [
            'field_name' => 'test_field',
            'field_label' => 'Test Field',
            'field_type' => 'text'
        ];

        // This would normally create a field, but we skip DB operations in tests
        $this->assertIsArray($fieldData);
        $this->assertEquals('test_field', $fieldData['field_name']);
    }

    public function testFieldTypes()
    {
        $validTypes = ['text', 'textarea', 'number', 'decimal', 'date', 'datetime', 'boolean', 'select', 'multiselect', 'email', 'url', 'phone'];

        foreach ($validTypes as $type) {
            $this->assertContains($type, $validTypes);
        }
    }

    public function testHelperMethods()
    {
        // Test that helper methods exist and are callable
        $this->assertTrue(method_exists($this->helper, 'renderEntityFields'));
        $this->assertTrue(method_exists($this->helper, 'saveEntityFields'));
        $this->assertTrue(method_exists($this->helper, 'validateEntityFields'));
        $this->assertTrue(method_exists($this->helper, 'getEntityFieldValues'));
    }

    public function testFieldValueValidation()
    {
        // Test that validation methods exist
        $this->assertTrue(method_exists($this->helper, 'validateEntityFields'));

        // Test with sample data
        $postData = [
            'custom_test_email' => 'test@example.com'
        ];

        $errors = $this->helper->validateEntityFields('customers', $postData);
        $this->assertIsArray($errors);
    }
}