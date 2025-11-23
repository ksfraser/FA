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

use FA\Interfaces\DatabaseRepositoryInterface;
use FA\Interfaces\DisplayServiceInterface;
use FA\Services\CompanyPrefsService;

/**
 * Data Checks Service
 *
 * Handles data validation checks for the application.
 * Refactored to OOP with SOLID principles and dependency injection.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages data checks only
 * - Open/Closed: Can be extended for additional checks
 * - Liskov Substitution: Compatible with check interfaces
 * - Interface Segregation: Focused check methods
 * - Dependency Inversion: Depends on abstractions, not globals
 *
 * DRY: Reuses data check logic across the application
 * TDD: Developed with unit tests for regression prevention
 *
 * UML Class Diagram:
 * +---------------------+
 * | DataChecksService  |
 * +---------------------+
 * | - databaseRepo     |
 * | - displayService   |
 * +---------------------+
 * | + dbHasCustomers() |
 * | + checkDbHasCustomers() |
 * | + dbHasCurrencies() |
 * | ...                 |
 * +---------------------+
 *
 * @package FA
 */
class DataChecksService {

    private DatabaseRepositoryInterface $databaseRepo;
    private DisplayServiceInterface $displayService;

    /**
     * Constructor with dependency injection
     *
     * @param DatabaseRepositoryInterface $databaseRepo Database repository
     * @param DisplayServiceInterface $displayService Display service
     */
    public function __construct(
        DatabaseRepositoryInterface $databaseRepo,
        DisplayServiceInterface $displayService
    ) {
        $this->databaseRepo = $databaseRepo;
        $this->displayService = $displayService;
    }

    /**
     * Check if database has customers
     *
     * @return bool True if has customers
     */
    public function dbHasCustomers(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "debtors_master");
    }

    /**
     * Check database has customers and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasCustomers(string $msg): void {
        if (!$this->dbHasCustomers()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has currencies
     *
     * @return bool True if has currencies
     */
    public function dbHasCurrencies(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "currencies");
    }

    /**
     * Check database has currencies and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasCurrencies(string $msg): void {
        if (!$this->dbHasCurrencies()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has currency rates
     *
     * @param string $currency Currency code
     * @param string $date Date
     * @param bool $msg Whether to display message
     * @return int Result
     */
    public function dbHasCurrencyRates(string $currency, string $date, bool $msg = false): int {
        $dateSql = DateService::date2sqlStatic($date);

        if (BankingService::isCompanyCurrencyStatic($currency)) {
            return 1;
        }
        $ret = $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "exchange_rates WHERE curr_code = '$currency' && date_ <= '$dateSql'");
        if ($ret == 0 && $msg) {
            $this->displayService->displayError(sprintf(_("Cannot retrieve exchange rate for currency %s as of %s. Please add exchange rate manually on Exchange Rates page."),
                $currency, $date), true);
        }
        return $ret;
    }

    /**
     * Check if database has tax types
     *
     * @return bool True if has tax types
     */
    public function dbHasTaxTypes(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "tax_types");
    }

    /**
     * Check database has tax types and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasTaxTypes(string $msg): void {
        if (!$this->dbHasTaxTypes()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has tax groups
     *
     * @return bool True if has tax groups
     */
    public function dbHasTaxGroups(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "tax_groups");
    }

    /**
     * Check database has tax groups and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasTaxGroups(string $msg): void {
        if (!$this->dbHasTaxGroups()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has sales types
     *
     * @return bool True if has sales types
     */
    public function dbHasSalesTypes(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "sales_types");
    }

    /**
     * Check database has sales types and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasSalesTypes(string $msg): void {
        if (!$this->dbHasSalesTypes()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if customer has branches
     *
     * @param string $customerId Customer ID
     * @return bool True if customer has branches
     */
    public function dbCustomerHasBranches(string $customerId): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "cust_branch "
            . "WHERE debtor_no=" . $this->databaseRepo->escape($customerId));
    }

    /**
     * Check if database has customer branches
     *
     * @return bool True if has customer branches
     */
    public function dbHasCustomerBranches(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM "
            . $this->databaseRepo->getTablePrefix() . "cust_branch WHERE !inactive");
    }

    /**
     * Check database has customer branches and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasCustomerBranches(string $msg): void {
        if (!$this->dbHasCustomerBranches()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has sales people
     *
     * @return bool True if has sales people
     */
    public function dbHasSalesPeople(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "salesman");
    }

    /**
     * Check database has sales people and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasSalesPeople(string $msg): void {
        if (!$this->dbHasSalesPeople()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has sales areas
     *
     * @return bool True if has sales areas
     */
    public function dbHasSalesAreas(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "areas");
    }

    /**
     * Check database has sales areas and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasSalesAreas(string $msg): void {
        if (!$this->dbHasSalesAreas()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has shippers
     *
     * @return bool True if has shippers
     */
    public function dbHasShippers(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "shippers");
    }

    /**
     * Check database has shippers and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasShippers(string $msg): void {
        if (!$this->dbHasShippers()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    // Add more methods similarly for all functions
    // For brevity, I'll add placeholders for the rest

    /**
     * Check if database has item tax types
     *
     * @return bool True if has item tax types
     */
    public function dbHasItemTaxTypes(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM " . $this->databaseRepo->getTablePrefix() . "item_tax_types");
    }

    /**
     * Check database has item tax types and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasItemTaxTypes(string $msg): void {
        if (!$this->dbHasItemTaxTypes()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has open workorders
     *
     * @return bool True if has open workorders
     */
    public function dbHasOpenWorkorders(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."workorders WHERE closed=0");
    }

    /**
     * Check if database has workorders
     *
     * @return bool True if has workorders
     */
    public function dbHasWorkorders(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."workorders");
    }

    /**
     * Check database has workorders and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasWorkorders(string $msg): void {
        if (!$this->dbHasWorkorders()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has open dimensions
     *
     * @return bool True if has open dimensions
     */
    public function dbHasOpenDimensions(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."dimensions WHERE closed=0");
    }

    /**
     * Check if database has dimensions
     *
     * @return bool True if has dimensions
     */
    public function dbHasDimensions(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."dimensions");
    }

    /**
     * Check database has dimensions and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasDimensions(string $msg): void {
        if (!$this->dbHasDimensions()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has suppliers
     *
     * @return bool True if has suppliers
     */
    public function dbHasSuppliers(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."suppliers");
    }

    /**
     * Check database has suppliers and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasSuppliers(string $msg): void {
        if (!$this->dbHasSuppliers()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has stock items
     *
     * @return bool True if has stock items
     */
    public function dbHasStockItems(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE mb_flag!='F'");
    }

    /**
     * Check database has stock items and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasStockItems(string $msg): void {
        if (!$this->dbHasStockItems()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has bom stock items
     *
     * @return bool True if has bom stock items
     */
    public function dbHasBomStockItems(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE mb_flag='M'");
    }

    /**
     * Check database has bom stock items and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasBomStockItems(string $msg): void {
        if (!$this->dbHasBomStockItems()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has manufacturable items
     *
     * @return bool True if has manufacturable items
     */
    public function dbHasManufacturableItems(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE (mb_flag='M')");
    }

    /**
     * Check database has manufacturable items and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasManufacturableItems(string $msg): void {
        if (!$this->dbHasManufacturableItems()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has purchasable items
     *
     * @return bool True if has purchasable items
     */
    public function dbHasPurchasableItems(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE mb_flag!='M'");
    }

    /**
     * Check database has purchasable items and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasPurchasableItems(string $msg): void {
        if (!$this->dbHasPurchasableItems()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has costable items
     *
     * @return bool True if has costable items
     */
    public function dbHasCostableItems(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE mb_flag!='D'");
    }

    /**
     * Check database has costable items and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasCostableItems(string $msg): void {
        if (!$this->dbHasCostableItems()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check database has fixed asset classes and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasFixedAssetClasses(string $msg): void {
        if (!$this->dbHasFixedAssetClasses()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has fixed asset classes
     *
     * @return bool True if has fixed asset classes
     */
    public function dbHasFixedAssetClasses(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_fa_class");
    }

    /**
     * Check if database has depreciable fixed assets
     *
     * @return bool True if has depreciable fixed assets
     */
    public function dbHasDepreciableFixedAssets(): bool {
        $year = \DateService::getCurrentFiscalYearStatic();
        $begin = \DateService::date2sqlStatic(\DateService::addMonthsStatic(\DateService::sql2dateStatic($year['begin']), -1));
        $end = \DateService::date2sqlStatic(\DateService::addMonthsStatic(\DateService::sql2dateStatic($year['end']), -1));

        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master 
            WHERE mb_flag='F'
                AND material_cost > 0
                AND stock_id IN ( SELECT stock_id FROM ".$this->databaseRepo->getTablePrefix()."stock_moves WHERE type=".\ST_SUPPRECEIVE." AND qty!=0 )
                AND stock_id NOT IN	( SELECT stock_id FROM ".$this->databaseRepo->getTablePrefix()."stock_moves WHERE (type=".\ST_CUSTDELIVERY." OR type=".\ST_INVADJUST.") AND qty!=0 )
                AND depreciation_date <= '".$end."'
                AND depreciation_date >='".$begin."'");
    }

    /**
     * Check database has depreciable fixed assets and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasDepreciableFixedAssets(string $msg): void {
        if (!$this->dbHasDepreciableFixedAssets()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has fixed assets
     *
     * @return bool True if has fixed assets
     */
    public function dbHasFixedAssets(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master WHERE mb_flag='F'");
    }

    /**
     * Check database has fixed assets and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasFixedAssets(string $msg): void {
        if (!$this->dbHasFixedAssets()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has purchasable fixed assets
     *
     * @return bool True if has purchasable fixed assets
     */
    public function dbHasPurchasableFixedAssets(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master 
            WHERE mb_flag='F'
                AND !inactive
                AND stock_id NOT IN
                    ( SELECT stock_id FROM ".$this->databaseRepo->getTablePrefix()."stock_moves WHERE type=".\ST_SUPPRECEIVE." AND qty!=0 )");
    }

    /**
     * Check database has purchasable fixed assets and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasPurchasableFixedAssets(string $msg): void {
        if (!$this->dbHasPurchasableFixedAssets()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has disposable fixed assets
     *
     * @return bool True if has disposable fixed assets
     */
    public function dbHasDisposableFixedAssets(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_master 
            WHERE mb_flag='F'
                AND !inactive
                AND stock_id IN
                    ( SELECT stock_id FROM ".$this->databaseRepo->getTablePrefix()."stock_moves WHERE type=".\ST_SUPPRECEIVE." AND qty!=0 )
                AND stock_id NOT IN
                    ( SELECT stock_id FROM ".$this->databaseRepo->getTablePrefix()."stock_moves WHERE (type=".\ST_CUSTDELIVERY." OR type=".\ST_INVADJUST.") AND qty!=0 )");
    }

    /**
     * Check database has disposable fixed assets and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasDisposableFixedAssets(string $msg): void {
        if (!$this->dbHasDisposableFixedAssets()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has stock categories
     *
     * @return bool True if has stock categories
     */
    public function dbHasStockCategories(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_category WHERE dflt_mb_flag!='F'");
    }

    /**
     * Check database has fixed asset categories and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasFixedAssetCategories(string $msg): void {
        if (!$this->dbHasFixedAssetCategories()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has fixed asset categories
     *
     * @return bool True if has fixed asset categories
     */
    public function dbHasFixedAssetCategories(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."stock_category WHERE dflt_mb_flag='F'");
    }

    /**
     * Check database has stock categories and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasStockCategories(string $msg): void {
        if (!$this->dbHasStockCategories()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has workcentres
     *
     * @return bool True if has workcentres
     */
    public function dbHasWorkcentres(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."workcentres");
    }

    /**
     * Check database has workcentres and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasWorkcentres(string $msg): void {
        if (!$this->dbHasWorkcentres()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has locations
     *
     * @return bool True if has locations
     */
    public function dbHasLocations(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."locations WHERE fixed_asset=0");
    }

    /**
     * Check database has locations and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasLocations(string $msg): void {
        if (!$this->dbHasLocations()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has bank accounts
     *
     * @return bool True if has bank accounts
     */
    public function dbHasBankAccounts(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."bank_accounts");
    }

    /**
     * Check database has bank accounts and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasBankAccounts(string $msg): void {
        if (!$this->dbHasBankAccounts()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has cash accounts
     *
     * @return bool True if has cash accounts
     */
    public function dbHasCashAccounts(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."bank_accounts
            WHERE account_type=3");
    }

    /**
     * Check if database has GL accounts
     *
     * @return bool True if has GL accounts
     */
    public function dbHasGlAccounts(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."chart_master");
    }

    /**
     * Check if database has GL account groups
     *
     * @return bool True if has GL account groups
     */
    public function dbHasGlAccountGroups(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."chart_types");
    }

    /**
     * Check database has GL account groups and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasGlAccountGroups(string $msg): void {
        if (!$this->dbHasGlAccountGroups()) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check if database has quick entries
     *
     * @return bool True if has quick entries
     */
    public function dbHasQuickEntries(): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."quick_entries");
    }

    /**
     * Check if database has tags
     *
     * @param int $type Tag type
     * @return bool True if has tags
     */
    public function dbHasTags(int $type): bool {
        return $this->databaseRepo->checkEmptyResult("SELECT COUNT(*) FROM ".$this->databaseRepo->getTablePrefix()."tags WHERE type=".$this->databaseRepo->escape($type));
    }

    /**
     * Check database has tags and display error if not
     *
     * @param int $type Tag type
     * @param string $msg Error message
     */
    public function checkDbHasTags(int $type, string $msg): void {
        if (!$this->dbHasTags($type)) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }



    /**
     * Integer input check
     * Return 1 if number has proper form and is within <min, max> range
     *
     * @param string $postname Post variable name
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     * @return int 1 if valid, 0 otherwise
     */
    public function checkInt(string $postname, ?int $min = null, ?int $max = null): int {
        if (!isset($_POST[$postname]))
            return 0;
        $num = \RequestService::inputNumStatic($postname);
        if (!is_int($num)) 
            return 0;
        if (isset($min) && ($num < $min)) 
            return 0;
        if (isset($max) && ($num > $max)) 
            return 0;
        return 1;
    }

    /**
     * Numeric input check
     * Return 1 if number has proper form and is within <min, max> range
     * Empty/not defined fields are defaulted to $dflt value
     *
     * @param string $postname Post variable name
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @param float $dflt Default value
     * @return int 1 if valid, 0 otherwise
     */
    public function checkNum(string $postname, ?float $min = null, ?float $max = null, float $dflt = 0): int {
        if (!isset($_POST[$postname]))
            return 0;
        $num = \RequestService::inputNumStatic($postname, $dflt);
        if ($num === false || $num === null) 
            return 0;
        if (isset($min) && ($num < $min)) 
            return 0;
        if (isset($max) && ($num > $max)) 
            return 0;
        return 1;
    }

    /**
     * Check if transaction is closed
     *
     * @param int $type Transaction type
     * @param int $typeNo Transaction number
     * @param string|null $msg Error message
     */
    public function checkIsClosed(int $type, int $typeNo, ?string $msg = null): void {
        if (($typeNo > 0) && \is_closed_trans($type, $typeNo)) {
            if (!$msg)
                $msg = sprintf(\_("%s #%s is closed for further edition."), $GLOBALS['systypes_array'][$type], $typeNo);
            $this->displayService->displayError($msg, true);
            $this->displayService->displayFooterExit();
        }
    }

    /**
     * Check database has template orders and display error if not
     *
     * @param string $msg Error message
     */
    public function checkDbHasTemplateOrders(string $msg): void {
        $sql = "SELECT sorder.order_no 
            FROM ".$this->databaseRepo->getTablePrefix()."sales_orders as sorder,"
                .$this->databaseRepo->getTablePrefix()."sales_order_details as line
            WHERE sorder.order_no = line.order_no AND sorder.type = 1
            GROUP BY line.order_no";

        if (!$this->databaseRepo->checkEmptyResult($sql)) {
            $this->displayService->displayError($msg, true);
            $this->displayService->endPage();
            exit;
        }
    }

    /**
     * Check deferred income account
     *
     * @param string $msg Error message
     */
    public function checkDeferredIncomeAct(string $msg): void {
        if (!CompanyPrefsService::getCompanyPref('deferred_income_act')) {
            $this->displayService->displayError($msg, true);
            $this->displayService->displayFooterExit();
        }
    }

    /**
     * Check if transaction is editable
     *
     * @param int $transType Transaction type
     * @param int $transNo Transaction number
     * @param string|null $msg Error message
     */
    public function checkIsEditable(int $transType, int $transNo, ?string $msg = null): void {
        if (!$_SESSION['wa_current_user']->can_access('SA_EDITOTHERSTRANS')) {
            $audit = \get_audit_trail_last($transType, $transNo);

            if ($_SESSION['wa_current_user']->user != $audit['user']) {
                if (!$msg)
                    $msg = '<b>'._("You have no edit access to transactions created by other users.").'</b>';
                $this->displayService->displayNote($msg);
                $this->displayService->displayFooterExit();
            }
        }
        if (!in_array($transType, array(\ST_SALESORDER, \ST_SALESQUOTE, \ST_PURCHORDER, \ST_WORKORDER)))
            $this->checkIsClosed($transType, $transNo, $msg);
    }

    /**
     * Check reference
     *
     * @param string $reference Reference
     * @param int $transType Transaction type
     * @param int $transNo Transaction number
     * @param mixed $context Context
     * @param mixed $line Line
     * @return bool True if valid
     */
    public function checkReference(string $reference, int $transType, int $transNo = 0, $context = null, $line = null): bool {
        if (!$GLOBALS['Refs']->is_valid($reference, $transType, $context, $line)) {
            $this->displayService->displayError(_("The entered reference is invalid."));
            return false;
        } elseif (!$GLOBALS['Refs']->is_new_reference($reference, $transType, $transNo)) {
            $this->displayService->displayError(_("The entered reference is already in use."));
            return false;
        }
        return true;
    }

    /**
     * Check system preference
     *
     * @param string $name Preference name
     * @param string $msg Error message
     * @param string $empty Empty value
     */
    public function checkSysPref(string $name, string $msg, string $empty = ''): void {
        if (CompanyPrefsService::getCompanyPref($name) === $empty) {
            $this->displayService->displayError($this->displayService->menuLink("/admin/gl_setup.php", $msg), true);
            $this->displayService->displayFooterExit();
        }
    }
}