<?php
/**
 * Data Checks Class Generator
 *
 * Generates all Query and Validator classes from configuration
 * Run once to generate the proper SOLID architecture
 */

namespace FA\Tools;

class DataChecksGenerator
{
    private const CHECKS = [
        // Basic entity checks
        ['name' => 'Customers', 'table' => 'debtors_master'],
        ['name' => 'Currencies', 'table' => 'currencies'],
        ['name' => 'SalesTypes', 'table' => 'sales_types'],
        ['name' => 'ItemTaxTypes', 'table' => 'item_tax_types'],
        ['name' => 'TaxTypes', 'table' => 'tax_types'],
        ['name' => 'TaxGroups', 'table' => 'tax_groups'],
        ['name' => 'CustomerBranches', 'table' => 'cust_branch', 'where' => '!inactive'],
        ['name' => 'SalesPeople', 'table' => 'salesman'],
        ['name' => 'SalesAreas', 'table' => 'areas'],
        ['name' => 'Shippers', 'table' => 'shippers'],
        ['name' => 'Workorders', 'table' => 'workorders'],
        ['name' => 'OpenWorkorders', 'table' => 'workorders', 'where' => 'closed=0'],
        ['name' => 'Dimensions', 'table' => 'dimensions'],
        ['name' => 'OpenDimensions', 'table' => 'dimensions', 'where' => 'closed=0'],
        ['name' => 'Suppliers', 'table' => 'suppliers'],
        ['name' => 'StockItems', 'table' => 'stock_master', 'where' => "mb_flag!='F'"],
        ['name' => 'BomStockItems', 'table' => 'stock_master', 'where' => "mb_flag='M'"],
        ['name' => 'ManufacturableItems', 'table' => 'stock_master', 'where' => "mb_flag='M'"],
        ['name' => 'PurchasableItems', 'table' => 'stock_master', 'where' => "mb_flag!='M'"],
        ['name' => 'CostableItems', 'table' => 'stock_master', 'where' => "mb_flag!='D'"],
        ['name' => 'FixedAssetClasses', 'table' => 'stock_fa_class'],
        ['name' => 'FixedAssets', 'table' => 'stock_master', 'where' => "mb_flag='F'"],
        ['name' => 'StockCategories', 'table' => 'stock_category', 'where' => "dflt_mb_flag!='F'"],
        ['name' => 'FixedAssetCategories', 'table' => 'stock_category', 'where' => "dflt_mb_flag='F'"],
        ['name' => 'Workcentres', 'table' => 'workcentres'],
        ['name' => 'Locations', 'table' => 'locations', 'where' => 'fixed_asset=0'],
        ['name' => 'BankAccounts', 'table' => 'bank_accounts'],
        ['name' => 'CashAccounts', 'table' => 'bank_accounts', 'where' => 'account_type=3'],
        ['name' => 'GlAccounts', 'table' => 'chart_master'],
        ['name' => 'GlAccountGroups', 'table' => 'chart_types'],
        ['name' => 'QuickEntries', 'table' => 'quick_entries'],
    ];

    public static function generateQueryClass(string $name, string $table, string $where = ''): string
    {
        $whereMethod = $where ? "\n    protected function getWhereClause(): string\n    {\n        return '{$where}';\n    }" : '';
        
        return <<<PHP
<?php
/**
 * Has {$name} Query
 *
 * Single Responsibility: Query if {$name} exist
 *
 * @package FA\DataChecks\Queries
 */

namespace FA\DataChecks\Queries;

use FA\DataChecks\AbstractDatabaseExistenceQuery;

class Has{$name}Query extends AbstractDatabaseExistenceQuery
{
    public function exists(): bool
    {
        return \$this->executeCountQuery();
    }

    protected function getTableName(): string
    {
        return '{$table}';
    }{$whereMethod}
}

PHP;
    }

    public static function generateValidatorClass(string $name): string
    {
        return <<<PHP
<?php
/**
 * {$name} Exist Validator
 *
 * Single Responsibility: Validate {$name} exist and handle errors
 *
 * @package FA\DataChecks\Validators
 */

namespace FA\DataChecks\Validators;

use FA\DataChecks\AbstractDatabaseExistenceValidator;

class {$name}ExistValidator extends AbstractDatabaseExistenceValidator
{
    // Inherits validate() method from base class
}

PHP;
    }

    public static function generateAll(string $basePath): void
    {
        foreach (self::CHECKS as $check) {
            $name = $check['name'];
            $table = $check['table'];
            $where = $check['where'] ?? '';

            // Generate Query
            $queryPath = $basePath . '/includes/DataChecks/Queries/Has' . $name . 'Query.php';
            file_put_contents($queryPath, self::generateQueryClass($name, $table, $where));

            // Generate Validator
            $validatorPath = $basePath . '/includes/DataChecks/Validators/' . $name . 'ExistValidator.php';
            file_put_contents($validatorPath, self::generateValidatorClass($name));

            echo "Generated: {$name}\n";
        }
    }
}

// Run if executed directly
if (php_sapi_name() === 'cli' && isset($argv[1])) {
    DataChecksGenerator::generateAll($argv[1]);
    echo "Done!\n";
}

PHP;
