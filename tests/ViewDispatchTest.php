<?php

use PHPUnit\Framework\TestCase;
use FA\ViewDispatch;
use FA\Dispatch;

/**
 * Unit tests for ViewDispatch
 *
 * Tests constructor dependency injection and delegation to model.
 * Ensures view logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests ViewDispatch only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | ViewDispatchTest   |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testConstructor() |
 * | + testGetSubTotal..()|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * |   ViewDispatch     |
 * +---------------------+
 *
 * @package FA
 */
class ViewDispatchTest extends TestCase
{
    /**
     * Test constructor injects model dependency
     */
    public function testConstructor()
    {
        $model = $this->createMock(Dispatch::class);
        $view = new ViewDispatch($model);
        $this->assertInstanceOf(ViewDispatch::class, $view);
    }

    /**
     * Test getSubTotal delegates to model
     */
    public function testGetSubTotalDelegatesToModel()
    {
        $model = $this->createMock(Dispatch::class);
        $model->method('getSubTotal')->willReturn(150.0);
        $view = new ViewDispatch($model);
        $this->assertEquals(150.0, $view->getSubTotal());
    }
}