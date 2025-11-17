<?php
/**
 * Data Checks Facade
 *
 * Facade pattern providing simple access to all data validation checks
 * Maintains backward compatibility with original procedural API
 *
 * SOLID:
 * - Single Responsibility: Coordinate validation checks
 * - Open/Closed: Can add new checks without modifying
 * - Liskov Substitution: All validators follow same interface
 * - Interface Segregation: Depends only on needed interfaces
 * - Dependency Inversion: Depends on abstractions (interfaces)
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\DatabaseQueryInterface;
use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\*;
use FA\DataChecks\Validators\*;

class DataChecksFacade
{
    private DatabaseQueryInterface $db;
    private ValidationErrorHandlerInterface $errorHandler;
    
    // Query instances (lazy loaded)
    private array $queries = [];
    
    // Validator instances (lazy loaded)
    private array $validators = [];

    public function __construct(
        DatabaseQueryInterface $db,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->db = $db;
        $this->errorHandler = $errorHandler;
    }

    // ===== Helper Methods =====
    
    private function getQuery(string $className): AbstractDatabaseExistenceQuery
    {
        if (!isset($this->queries[$className])) {
            $fullClass = "FA\\DataChecks\\Queries\\{$className}Query";
            $this->queries[$className] = new $fullClass($this->db);
        }
        return $this->queries[$className];
    }
    
    private function getValidator(string $className): AbstractDatabaseExistenceValidator
    {
        if (!isset($this->validators[$className])) {
            $query = $this->getQuery($className);
            $fullClass = "FA\\DataChecks\\Validators\\{$className}ExistValidator";
            $this->validators[$className] = new $fullClass($query, $this->errorHandler);
        }
        return $this->validators[$className];
    }

    // ===== Query Methods (db_has_x) =====

    public function dbHasCustomers(): bool { return $this->getQuery('HasCustomers')->exists(); }
    public function dbHasCurrencies(): bool { return $this->getQuery('HasCurrencies')->exists(); }
    public function dbHasSalesTypes(): bool { return $this->getQuery('HasSalesTypes')->exists(); }
    public function dbHasItemTaxTypes(): bool { return $this->getQuery('HasItemTaxTypes')->exists(); }
    public function dbHasTaxTypes(): bool { return $this->getQuery('HasTaxTypes')->exists(); }
    public function dbHasTaxGroups(): bool { return $this->getQuery('HasTaxGroups')->exists(); }
    public function dbHasCustomerBranches(): bool { return $this->getQuery('HasCustomerBranches')->exists(); }
    public function dbHasSalesPeople(): bool { return $this->getQuery('HasSalesPeople')->exists(); }
    public function dbHasSalesAreas(): bool { return $this->getQuery('HasSalesAreas')->exists(); }
    public function dbHasShippers(): bool { return $this->getQuery('HasShippers')->exists(); }
    public function dbHasWorkorders(): bool { return $this->getQuery('HasWorkorders')->exists(); }
    public function dbHasOpenWorkorders(): bool { return $this->getQuery('HasOpenWorkorders')->exists(); }
    public function dbHasDimensions(): bool { return $this->getQuery('HasDimensions')->exists(); }
    public function dbHasOpenDimensions(): bool { return $this->getQuery('HasOpenDimensions')->exists(); }
    public function dbHasSuppliers(): bool { return $this->getQuery('HasSuppliers')->exists(); }
    public function dbHasStockItems(): bool { return $this->getQuery('HasStockItems')->exists(); }
    public function dbHasBomStockItems(): bool { return $this->getQuery('HasBomStockItems')->exists(); }
    public function dbHasManufacturableItems(): bool { return $this->getQuery('HasManufacturableItems')->exists(); }
    public function dbHasPurchasableItems(): bool { return $this->getQuery('HasPurchasableItems')->exists(); }
    public function dbHasCostableItems(): bool { return $this->getQuery('HasCostableItems')->exists(); }
    public function dbHasFixedAssetClasses(): bool { return $this->getQuery('HasFixedAssetClasses')->exists(); }
    public function dbHasFixedAssets(): bool { return $this->getQuery('HasFixedAssets')->exists(); }
    public function dbHasStockCategories(): bool { return $this->getQuery('HasStockCategories')->exists(); }
    public function dbHasFixedAssetCategories(): bool { return $this->getQuery('HasFixedAssetCategories')->exists(); }
    public function dbHasWorkcentres(): bool { return $this->getQuery('HasWorkcentres')->exists(); }
    public function dbHasLocations(): bool { return $this->getQuery('HasLocations')->exists(); }
    public function dbHasBankAccounts(): bool { return $this->getQuery('HasBankAccounts')->exists(); }
    public function dbHasCashAccounts(): bool { return $this->getQuery('HasCashAccounts')->exists(); }
    public function dbHasGlAccounts(): bool { return $this->getQuery('HasGlAccounts')->exists(); }
    public function dbHasGlAccountGroups(): bool { return $this->getQuery('HasGlAccountGroups')->exists(); }
    public function dbHasQuickEntries(): bool { return $this->getQuery('HasQuickEntries')->exists(); }

    // ===== Validator Methods (check_db_has_x) =====

    public function checkDbHasCustomers(string $msg): void { $this->getValidator('HasCustomers')->validate($msg); }
    public function checkDbHasCurrencies(string $msg): void { $this->getValidator('HasCurrencies')->validate($msg); }
    public function checkDbHasSalesTypes(string $msg): void { $this->getValidator('HasSalesTypes')->validate($msg); }
    public function checkDbHasItemTaxTypes(string $msg): void { $this->getValidator('HasItemTaxTypes')->validate($msg); }
    public function checkDbHasTaxTypes(string $msg): void { $this->getValidator('HasTaxTypes')->validate($msg); }
    public function checkDbHasTaxGroups(string $msg): void { $this->getValidator('HasTaxGroups')->validate($msg); }
    public function checkDbHasCustomerBranches(string $msg): void { $this->getValidator('HasCustomerBranches')->validate($msg); }
    public function checkDbHasSalesPeople(string $msg): void { $this->getValidator('HasSalesPeople')->validate($msg); }
    public function checkDbHasSalesAreas(string $msg): void { $this->getValidator('HasSalesAreas')->validate($msg); }
    public function checkDbHasShippers(string $msg): void { $this->getValidator('HasShippers')->validate($msg); }
    public function checkDbHasWorkorders(string $msg): void { $this->getValidator('HasWorkorders')->validate($msg); }
    public function checkDbHasDimensions(string $msg): void { $this->getValidator('HasDimensions')->validate($msg); }
    public function checkDbHasSuppliers(string $msg): void { $this->getValidator('HasSuppliers')->validate($msg); }
    public function checkDbHasStockItems(string $msg): void { $this->getValidator('HasStockItems')->validate($msg); }
    public function checkDbHasBomStockItems(string $msg): void { $this->getValidator('HasBomStockItems')->validate($msg); }
    public function checkDbHasManufacturableItems(string $msg): void { $this->getValidator('HasManufacturableItems')->validate($msg); }
    public function checkDbHasPurchasableItems(string $msg): void { $this->getValidator('HasPurchasableItems')->validate($msg); }
    public function checkDbHasCostableItems(string $msg): void { $this->getValidator('HasCostableItems')->validate($msg); }
    public function checkDbHasFixedAssetClasses(string $msg): void { $this->getValidator('HasFixedAssetClasses')->validate($msg); }
    public function checkDbHasFixedAssets(string $msg): void { $this->getValidator('HasFixedAssets')->validate($msg); }
    public function checkDbHasStockCategories(string $msg): void { $this->getValidator('HasStockCategories')->validate($msg); }
    public function checkDbHasFixedAssetCategories(string $msg): void { $this->getValidator('HasFixedAssetCategories')->validate($msg); }
    public function checkDbHasWorkcentres(string $msg): void { $this->getValidator('HasWorkcentres')->validate($msg); }
    public function checkDbHasLocations(string $msg): void { $this->getValidator('HasLocations')->validate($msg); }
    public function checkDbHasBankAccounts(string $msg): void { $this->getValidator('HasBankAccounts')->validate($msg); }
    public function checkDbHasGlAccountGroups(string $msg): void { $this->getValidator('HasGlAccountGroups')->validate($msg); }

    // ===== Parameterized Query Methods =====

    /**
     * Check if customer has branches
     *
     * @param string $customerId Customer ID
     * @return bool True if customer has branches
     */
    public function dbCustomerHasBranches(string $customerId): bool
    {
        $query = new Queries\HasCustomerBranchesForCustomerQuery($this->db, $customerId);
        return $query->exists();
    }

    /**
     * Check if database has tags (optionally filtered by type)
     *
     * @param int|null $type Tag type filter
     * @return bool True if has tags
     */
    public function dbHasTags(?int $type = null): bool
    {
        $query = new Queries\HasTagsForTypeQuery($this->db, $type);
        return $query->exists();
    }

    /**
     * Check if database has currency rates for given currency and date
     *
     * @param string $currency Currency code
     * @param string $date Date in database format
     * @param string $msg Error message to display if not found
     * @return bool True if has rates
     */
    public function dbHasCurrencyRates(string $currency, string $date, string $msg): bool
    {
        $query = new Queries\HasCurrencyRatesQuery($this->db, $currency, $date);
        if (!$query->exists()) {
            $validator = new Validators\CurrencyRatesExistValidator($query, $this->errorHandler);
            $validator->validate($msg);
            return false;
        }
        return true;
    }

    /**
     * Execute arbitrary SQL and check if it returns results
     *
     * @param string $sql SQL query
     * @return bool True if query returns results
     */
    public function checkEmptyResult(string $sql): bool
    {
        $query = new Queries\ArbitrarySqlQuery($this->db, $sql);
        return $query->hasResults();
    }

    // ===== Parameterized Validator Methods =====

    /**
     * Validate customer has branches, display error if not
     *
     * @param string $customerId Customer ID
     * @param string $msg Error message
     * @return void
     */
    public function checkDbCustomerHasBranches(string $customerId, string $msg): void
    {
        $query = new Queries\HasCustomerBranchesForCustomerQuery($this->db, $customerId);
        $validator = new Validators\CustomerBranchesForCustomerExistValidator($query, $this->errorHandler);
        $validator->validate($msg);
    }

    /**
     * Validate database has tags, display error if not
     *
     * @param string $msg Error message
     * @param int|null $type Tag type filter
     * @return void
     */
    public function checkDbHasTags(string $msg, ?int $type = null): void
    {
        $query = new Queries\HasTagsForTypeQuery($this->db, $type);
        $validator = new Validators\TagsForTypeExistValidator($query, $this->errorHandler);
        $validator->validate($msg);
    }

    // ===== Input Validation Methods =====

    /**
     * Validate POST integer parameter
     *
     * @param string $postname POST parameter name
     * @param int|null $min Minimum value
     * @param int|null $max Maximum value
     * @return bool True if valid
     */
    public function checkInt(string $postname, ?int $min = null, ?int $max = null): bool
    {
        $validator = new Validators\PostIntegerValidator();
        return $validator->validate($postname, $min, $max);
    }

    /**
     * Validate POST numeric parameter
     *
     * @param string $postname POST parameter name
     * @param float|null $min Minimum value
     * @param float|null $max Maximum value
     * @param float $default Default value
     * @return bool True if valid
     */
    public function checkNum(string $postname, ?float $min = null, ?float $max = null, float $default = 0): bool
    {
        $validator = new Validators\PostNumericValidator();
        return $validator->validate($postname, $min, $max, $default);
    }

    // ===== Transaction Validation Methods =====

    /**
     * Check if transaction is closed, display error if so
     *
     * @param int $type Transaction type
     * @param int $typeNo Transaction number
     * @param string|null $msg Optional error message
     * @return void
     */
    public function checkIsClosed(int $type, int $typeNo, ?string $msg = null): void
    {
        $query = new Queries\TransactionIsClosedQuery($this->db, $type, $typeNo);
        $validator = new Validators\TransactionNotClosedValidator($query, $this->errorHandler);
        $validator->validate($msg);
    }

    /**
     * Check if transaction is editable by current user
     *
     * @param int $transType Transaction type
     * @param int $transNo Transaction number
     * @param string|null $msg Optional error message
     * @return void
     */
    public function checkIsEditable(int $transType, int $transNo, ?string $msg = null): void
    {
        $query = new Queries\TransactionIsEditableQuery($this->db, $transType, $transNo);
        $validator = new Validators\TransactionEditableValidator($query, $this->errorHandler);
        $validator->validate($msg);
    }

    /**
     * Validate reference is valid and unique
     *
     * @param string $reference Reference to validate
     * @param int $transType Transaction type
     * @param int $transNo Transaction number (0 for new)
     * @param mixed $context Context for validation
     * @param mixed $line Line for validation
     * @return bool True if valid
     */
    public function checkReference(
        string $reference,
        int $transType,
        int $transNo = 0,
        $context = null,
        $line = null
    ): bool {
        $validator = new Validators\ReferenceValidator();
        return $validator->validate($reference, $transType, $transNo, $context, $line);
    }

    // ===== System Configuration Validation =====

    /**
     * Check if database has template orders, display error if not
     *
     * @param string $msg Error message
     * @return void
     */
    public function checkDbHasTemplateOrders(string $msg): void
    {
        $query = new Queries\HasTemplateOrdersQuery($this->db);
        $validator = new Validators\TemplateOrdersExistValidator($query, $this->errorHandler);
        $validator->validate($msg);
    }

    /**
     * Check if deferred income account is configured
     *
     * @param string $msg Error message
     * @return void
     */
    public function checkDeferredIncomeAct(string $msg): void
    {
        if (!\get_company_pref('deferred_income_act')) {
            $this->errorHandler->handleValidationError($msg);
        }
    }

    /**
     * Validate system preference is set
     *
     * @param string $name Preference name
     * @param string $msg Error message
     * @param string $empty Value considered empty
     * @return void
     */
    public function checkSysPref(string $name, string $msg, string $empty = ''): void
    {
        $validator = new Validators\SystemPreferenceValidator($this->errorHandler);
        $validator->validate($name, $msg, $empty);
    }
}
