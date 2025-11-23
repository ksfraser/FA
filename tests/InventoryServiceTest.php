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
 * | + testIsManufacturedStatic()|
 * | + testIsPurchasedStatic()|
 * | + testIsServiceStatic()|
 * | + testIsFixedAssetStatic()|
 * | + testHasStockHoldingStatic()|
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

    /**
     * Test static isManufacturedStatic method
     */
    public function testIsManufacturedStatic()
    {
        $this->assertTrue(InventoryService::isManufacturedStatic('M'));
        $this->assertFalse(InventoryService::isManufacturedStatic('B'));
        $this->assertFalse(InventoryService::isManufacturedStatic('D'));
        $this->assertFalse(InventoryService::isManufacturedStatic('F'));
    }

    /**
     * Test static isPurchasedStatic method
     */
    public function testIsPurchasedStatic()
    {
        $this->assertTrue(InventoryService::isPurchasedStatic('B'));
        $this->assertFalse(InventoryService::isPurchasedStatic('M'));
        $this->assertFalse(InventoryService::isPurchasedStatic('D'));
        $this->assertFalse(InventoryService::isPurchasedStatic('F'));
    }

    /**
     * Test static isServiceStatic method
     */
    public function testIsServiceStatic()
    {
        $this->assertTrue(InventoryService::isServiceStatic('D'));
        $this->assertFalse(InventoryService::isServiceStatic('M'));
        $this->assertFalse(InventoryService::isServiceStatic('B'));
        $this->assertFalse(InventoryService::isServiceStatic('F'));
    }

    /**
     * Test static isFixedAssetStatic method
     */
    public function testIsFixedAssetStatic()
    {
        $this->assertTrue(InventoryService::isFixedAssetStatic('F'));
        $this->assertFalse(InventoryService::isFixedAssetStatic('M'));
        $this->assertFalse(InventoryService::isFixedAssetStatic('B'));
        $this->assertFalse(InventoryService::isFixedAssetStatic('D'));
    }

    /**
     * Test static hasStockHoldingStatic method
     */
    public function testHasStockHoldingStatic()
    {
        $this->assertTrue(InventoryService::hasStockHoldingStatic('M'));
        $this->assertTrue(InventoryService::hasStockHoldingStatic('B'));
        $this->assertFalse(InventoryService::hasStockHoldingStatic('D'));
        $this->assertFalse(InventoryService::hasStockHoldingStatic('F'));
    }
}