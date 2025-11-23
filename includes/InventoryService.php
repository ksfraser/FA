<?php

namespace FA;

use FA\Interfaces\ItemRepositoryInterface;

/**
 * Inventory Service
 *
 * Handles inventory-related checks and operations with DI support.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages inventory logic only
 * - Open/Closed: Can be extended for additional inventory features
 * - Liskov Substitution: Compatible with inventory interfaces
 * - Interface Segregation: Focused inventory methods
 * - Dependency Inversion: Depends on abstractions via DI
 *
 * DRY: Reuses inventory logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | InventoryService   |
 * +---------------------+
 * | - itemRepo         |
 * +---------------------+
 * | + __construct()    |
 * | + isManufactured() |
 * | + isPurchased()    |
 * | + isService()      |
 * | + isFixedAsset()   |
 * | + hasStockHolding()|
 * +---------------------+
 *
 * @package FA
 */
class InventoryService
{
    private ?ItemRepositoryInterface $itemRepo;

    /**
     * Constructor with optional dependency injection
     *
     * @param ItemRepositoryInterface|null $itemRepo Item repository
     */
    public function __construct(?ItemRepositoryInterface $itemRepo = null)
    {
        $this->itemRepo = $itemRepo ?? new ProductionItemRepository();
    }
    /**
     * Check if item is manufactured
     *
     * @param string $mb_flag Manufacturing flag
     * @return bool True if manufactured
     */
    public function isManufactured(string $mb_flag): bool
    {
        return ($mb_flag == 'M');
    }

    /**
     * Check if item is purchased
     *
     * @param string $mb_flag Manufacturing flag
     * @return bool True if purchased
     */
    public function isPurchased(string $mb_flag): bool
    {
        return ($mb_flag == 'B');
    }

    /**
     * Check if item is service
     *
     * @param string $mb_flag Manufacturing flag
     * @return bool True if service
     */
    public function isService(string $mb_flag): bool
    {
        return ($mb_flag == 'D');
    }

    /**
     * Check if item is fixed asset
     *
     * @param string $mb_flag Manufacturing flag
     * @return bool True if fixed asset
     */
    public function isFixedAsset(string $mb_flag): bool
    {
        return ($mb_flag == 'F');
    }

    /**
     * Check if item has stock holding
     *
     * @param string $mb_flag Manufacturing flag
     * @return bool True if has stock holding
     */
    public function hasStockHolding(string $mb_flag): bool
    {
        return $this->isPurchased($mb_flag) || $this->isManufactured($mb_flag);
    }

    /**
     * Check if item is an inventory item (not a service)
     *
     * @param string $stock_id Stock ID
     * @return bool True if inventory item
     */
    public static function isInventoryItem(string $stock_id): bool
    {
        return \is_inventory_item($stock_id);
    }

    /**
     * Get quantity on hand on date
     *
     * @param string $stock_id Stock ID
     * @param string|null $location Location code
     * @param string|null $date Date (defaults to today)
     * @return float Quantity on hand
     */
    public static function getQohOnDate(string $stock_id, ?string $location = null, ?string $date = null): float
    {
        return \get_qoh_on_date($stock_id, $location, $date);
    }
}