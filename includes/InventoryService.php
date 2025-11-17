<?php

namespace FA;

/**
 * Inventory Service
 *
 * Handles inventory-related checks and operations.
 * Refactored from procedural functions to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages inventory logic only
 * - Open/Closed: Can be extended for additional inventory features
 * - Liskov Substitution: Compatible with inventory interfaces
 * - Interface Segregation: Focused inventory methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses inventory logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | InventoryService   |
 * +---------------------+
 * |                    |
 * +---------------------+
 * | + isManufactured(flag)|
 * | + isPurchased(flag) |
 * | + isService(flag)   |
 * | + isFixedAsset(flag)|
 * | + hasStockHolding(flag)|
 * +---------------------+
 *
 * @package FA
 */
class InventoryService
{
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
}