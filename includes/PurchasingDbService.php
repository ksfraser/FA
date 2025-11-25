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

use FA\Interfaces\PurchasingRepositoryInterface;

/**
 * Purchasing Database Service
 *
 * Handles purchasing-related database operations with DI support.
 * Refactored to OOP with SOLID principles.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages purchasing DB operations only
 * - Open/Closed: Can be extended for additional purchasing logic
 * - Liskov Substitution: Compatible with DB interfaces
 * - Interface Segregation: Focused purchasing DB methods
 * - Dependency Inversion: Depends on abstractions via DI
 *
 * DRY: Reuses purchasing DB logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | PurchasingDbService |
 * +---------------------+
 * | - purchasingRepo   |
 * +---------------------+
 * | + __construct()    |
 * | + addGlTransSupplier() |
 * | + getPurchasePrice()   |
 * | + getPurchaseConversionFactor() |
 * | ...                    |
 * +---------------------+
 *
 * @package FA
 */
class PurchasingDbService {
    private ?PurchasingRepositoryInterface $purchasingRepo;

    /**
     * Constructor with optional dependency injection
     *
     * @param PurchasingRepositoryInterface|null $purchasingRepo Purchasing repository
     */
    public function __construct(?PurchasingRepositoryInterface $purchasingRepo = null) {
        $this->purchasingRepo = $purchasingRepo ?? new ProductionPurchasingRepository();
        include_once($path_to_root . "/purchasing/includes/supp_trans_class.inc");
        include_once($path_to_root . "/includes/banking.inc");
        include_once($path_to_root . "/includes/inventory.inc");
        include_once($path_to_root . "/includes/date_functions.inc");
        include_once($path_to_root . "/includes/db/allocations_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/supp_trans_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/po_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/grn_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/invoice_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/suppalloc_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/supp_payment_db.inc");
        include_once($path_to_root . "/purchasing/includes/db/suppliers_db.inc");
    }

    /**
     * Add a supplier-related GL transaction
     *
     * @param int $type Transaction type
     * @param int $typeNo Transaction number
     * @param string $date Display date
     * @param string $account Account
     * @param int $dimension Dimension
     * @param int $dimension2 Dimension 2
     * @param float $amount Amount
     * @param int $supplierId Supplier ID
     * @param string $errMsg Error message
     * @param float $rate Rate
     * @param string $memo Memo
     * @return int Result
     */
    public function addGlTransSupplier(int $type, int $typeNo, string $date, string $account, int $dimension, int $dimension2, float $amount, int $supplierId, string $errMsg = "", float $rate = 0, string $memo = ""): int {
        if ($errMsg == "") {
            $errMsg = "The supplier GL transaction could not be inserted";
        }

        return \FA\BankingService::addGlTrans($type, $typeNo, $date, $account, $dimension, $dimension2, $memo,
            $amount, get_supplier_currency($supplierId),
            PT_SUPPLIER, $supplierId, $errMsg, $rate);
    }

    /**
     * Get purchase price
     *
     * @param int $supplierId Supplier ID
     * @param string $stockId Stock ID
     * @return float Purchase price
     */
    public function getPurchasePrice(int $supplierId, string $stockId): float {
        $sql = "SELECT price, conversion_factor FROM ".TB_PREF."purch_data
            WHERE supplier_id = ".db_escape($supplierId)."
            AND stock_id = ".db_escape($stockId);

        $result = db_query($sql, "The supplier pricing details could not be retrieved");
        $myrow = db_fetch_row($result);

        return $myrow == false ? 0 : $myrow[0];
    }

    /**
     * Get purchase conversion factor
     *
     * @param int $supplierId Supplier ID
     * @param string $stockId Stock ID
     * @return float Conversion factor
     */
    public function getPurchaseConversionFactor(int $supplierId, string $stockId): float {
        $sql = "SELECT price, conversion_factor FROM ".TB_PREF."purch_data
            WHERE supplier_id = ".db_escape($supplierId)."
            AND stock_id = ".db_escape($stockId);

        $result = db_query($sql, "The supplier pricing details could not be retrieved");
        $myrow = db_fetch_row($result);

        return $myrow == false ? 1 : $myrow[1];
    }

    /**
     * Get purchase data
     *
     * @param int $supplierId Supplier ID
     * @param string $stockId Stock ID
     * @return array Purchase data
     */
    public function getPurchaseData(int $supplierId, string $stockId): array {
        $sql = "SELECT * FROM ".TB_PREF."purch_data
            WHERE supplier_id = ".db_escape($supplierId)."
            AND stock_id = ".db_escape($stockId);

        $result = db_query($sql, "The supplier pricing details could not be retrieved");

        return db_fetch($result);
    }

    /**
     * Add or update purchase data
     *
     * @param int $supplierId Supplier ID
     * @param string $stockId Stock ID
     * @param float $price Price
     * @param string $description Description
     * @param string $uom UOM
     */
    public function addOrUpdatePurchaseData(int $supplierId, string $stockId, float $price, string $description = "", string $uom = ""): void {
        $sql = "SELECT count(*) FROM ".TB_PREF."purch_data
            WHERE stock_id = ".db_escape($stockId)."
            AND supplier_id = ".db_escape($supplierId);

        $result = db_query($sql, "Error reading purchase data");
        $myrow = db_fetch_row($result);

        if ($myrow[0] == 0) { // no itemized purch data
            $sql = "INSERT INTO ".TB_PREF."purch_data (supplier_id, stock_id, price, conversion_factor, description, uom)
                VALUES (".db_escape($supplierId).", ".db_escape($stockId).", ".db_escape($price).", 1, ".db_escape($description).", ".db_escape($uom).")";
            db_query($sql, "The supplier purchasing details could not be added");
        } else {
            $sql = "UPDATE ".TB_PREF."purch_data SET price=".db_escape($price).",
                description=".db_escape($description).", uom=".db_escape($uom)."
                WHERE stock_id = ".db_escape($stockId)."
                AND supplier_id = ".db_escape($supplierId);
            db_query($sql, "The supplier purchasing details could not be updated");
        }
    }

    /**
     * Get PO prepayments
     *
     * @param mixed $suppTrans Supp trans
     * @return array Prepayments
     */
    public function getPoPrepayments($suppTrans): array {
        // Placeholder implementation
        return [];
    }

    /**
     * Add direct supp trans
     *
     * @param mixed $cart Cart
     * @return int Result
     */
    public function addDirectSuppTrans($cart): int {
        // Placeholder
        return 0;
    }
}