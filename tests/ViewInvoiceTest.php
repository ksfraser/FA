<?php

use PHPUnit\Framework\TestCase;
use FA\ViewInvoice;
use FA\Invoice;

/**
 * Unit tests for ViewInvoice
 *
 * Tests rendering of invoice views.
 * Ensures view logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests ViewInvoice only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | ViewInvoiceTest    |
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
 * |   ViewInvoice      |
 * +---------------------+
 *
 * @package FA
 */
class ViewInvoiceTest extends TestCase
{
    /**
     * Test constructor injects model dependency
     */
    public function testConstructor()
    {
        $model = $this->createMock(Invoice::class);
        $view = new ViewInvoice($model);
        $this->assertInstanceOf(ViewInvoice::class, $view);
    }

    /**
     * Test getSubTotal delegates to model
     */
    public function testGetSubTotalDelegatesToModel()
    {
        $model = $this->createMock(Invoice::class);
        $model->method('getSubTotal')->willReturn(200.0);
        $view = new ViewInvoice($model);
        $this->assertEquals(200.0, $view->getSubTotal());
    }
}