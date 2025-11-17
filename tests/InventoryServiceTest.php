<?php

use PHPUnit\Framework\TestCase;
use FA\InventoryService;

/**
 * Unit tests for InventoryService
 *
 * Tests inventory checks and operations.
 * Ensures inventory logic is separated and testable.
 *
 * SOLID Principles:
 * - Single Responsibility: Tests InventoryService only
 * - Open/Closed: Can add more tests without modifying existing
 * - Liskov Substitution: Uses mocks for dependencies
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Mocks abstract dependencies
 *
 * TDD: Tests written before implementation, ensuring coverage.
 *
 * UML Class Diagram:
 * +---------------------+
 * | InventoryServiceTest|
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + testIsManufactured()|
 * | + testHasStockHolding()|
 * +---------------------+
 *           |
 *           | tests
 *           v
 * +---------------------+
 * | InventoryService   |
 * +---------------------+
 *
 * @package FA
 */
class InventoryServiceTest extends TestCase
{
    /**
     * Test isManufactured method
     */
    public function testIsManufactured()
    {
        $is = new InventoryService();
        $this->assertTrue($is->isManufactured('M'));
        $this->assertFalse($is->isManufactured('B'));
    }

    /**
     * Test hasStockHolding method
     */
    public function testHasStockHolding()
    {
        $is = new InventoryService();
        $this->assertTrue($is->hasStockHolding('M'));
        $this->assertTrue($is->hasStockHolding('B'));
        $this->assertFalse($is->hasStockHolding('D'));
    }
}