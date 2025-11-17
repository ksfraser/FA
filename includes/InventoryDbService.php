<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
namespace FA;

use FA\Interfaces\InventoryRepositoryInterface;

/**
 * Inventory Database Service
 *
 * Handles inventory-related database operations with DI support.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages inventory DB operations only
 * - Open/Closed: Can be extended for additional inventory logic
 * - Liskov Substitution: Compatible with DB interfaces
 * - Interface Segregation: Focused inventory DB methods
 * - Dependency Inversion: Depends on abstractions via DI
 *
 * DRY: Reuses inventory DB logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | InventoryDbService |
 * +---------------------+
 * | - inventoryRepo    |
 * +---------------------+
 * | + __construct()    |
 * | + itemImgName()    |
 * | + getStockMovements() |
 * | + calculateReorderLevel() |
 * | + sendReorderEmail() |
 * +---------------------+
 *
 * @package FA
 */
class InventoryDbService {
    private ?InventoryRepositoryInterface $inventoryRepo;

    /**
     * Constructor with optional dependency injection
     *
     * @param InventoryRepositoryInterface|null $inventoryRepo Inventory repository
     */
    public function __construct(?InventoryRepositoryInterface $inventoryRepo = null) {
        $this->inventoryRepo = $inventoryRepo ?? new ProductionInventoryRepository();
        include_once($path_to_root . "/includes/date_functions.inc");
        include_once($path_to_root . "/includes/banking.inc");
        include_once($path_to_root . "/includes/inventory.inc");
        include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_trans_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_prices_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_purchases_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_codes_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_locations_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_adjust_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_transfer_db.inc");
        include_once($path_to_root . "/inventory/includes/db/items_units_db.inc");
    }

    /**
     * Get item image name
     *
     * @param string $stockId Stock ID
     * @return string Image name
     */
    public function itemImgName(string $stockId): string {
        $stockId = strtr($stockId, "><\\/:|*?", '________');
        return clean_file_name($stockId);
    }

    /**
     * Get stock movements
     *
     * @param string $stockId Stock ID
     * @param string|null $stockLocation Stock location
     * @param string $beforeDate Before date
     * @param string $afterDate After date
     * @return array Stock movements
     */
    public function getStockMovements(string $stockId, ?string $stockLocation, string $beforeDate, string $afterDate): array {
        // Placeholder implementation
        return [];
    }

    /**
     * Calculate reorder level
     *
     * @param string $location Location
     * @param mixed $line Line
     * @param array $stIds ST IDs
     * @param array $stNames ST names
     * @param array $stNum ST num
     * @param array $stReorder ST reorder
     */
    public function calculateReorderLevel(string $location, $line, array &$stIds, array &$stNames, array &$stNum, array &$stReorder): void {
        // Placeholder
    }

    /**
     * Send reorder email
     *
     * @param string $loc Location
     * @param array $stIds ST IDs
     * @param array $stNames ST names
     * @param array $stNum ST num
     * @param array $stReorder ST reorder
     */
    public function sendReorderEmail(string $loc, array $stIds, array $stNames, array $stNum, array $stReorder): void {
        // Placeholder
    }
}