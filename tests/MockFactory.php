<?php
/**
 * Mock Factory for FrontAccounting Tests
 *
 * Provides mock implementations of global functions for unit testing.
 * Enables isolated testing of services without database dependencies.
 *
 * SOLID Principles:
 * - Single Responsibility: Manages test mocks only
 * - Open/Closed: Can extend with new mocks without modifying existing
 * - Dependency Inversion: Provides abstractions for testing
 *
 * Usage:
 *   MockFactory::init();
 *   MockFactory::setCompanyPref('curr_default', 'USD');
 *   MockFactory::setExchangeRate('EUR', '2025-01-01', 1.18);
 *
 * @package FA\Tests
 */

namespace FA\Tests;

class MockFactory {
    private static $companyPrefs = [];
    private static $exchangeRates = [];
    private static $dbResults = [];
    private static $errors = [];
    private static $glTransactions = [];
    
    /**
     * Initialize mock factory with defaults
     */
    public static function init(): void {
        self::$companyPrefs = [
            'curr_default' => 'USD',
            'exchange_diff_act' => '1000',
            'deferred_income_act' => '2000'
        ];
        self::$exchangeRates = [];
        self::$dbResults = [];
        self::$errors = [];
        self::$glTransactions = [];
        
        // Define global constants if not already defined
        if (!defined('TB_PREF')) {
            define('TB_PREF', '0_');
        }
        if (!defined('PT_CUSTOMER')) {
            define('PT_CUSTOMER', 2);
        }
        if (!defined('PT_SUPPLIER')) {
            define('PT_SUPPLIER', 3);
        }
    }
    
    /**
     * Set a company preference
     */
    public static function setCompanyPref(string $key, $value): void {
        self::$companyPrefs[$key] = $value;
    }
    
    /**
     * Set an exchange rate
     *
     * @param string $currency Currency code
     * @param string $date Date
     * @param float $rate Exchange rate
     */
    public static function setExchangeRate(string $currency, string $date, float $rate): void {
        self::$exchangeRates[$currency . '_' . $date] = [
            'rate_buy' => $rate,
            'rate_sell' => $rate,
            'date_' => $date
        ];
    }
    
    /**
     * Set a database query result
     */
    public static function setDbResult(string $queryPattern, array $result): void {
        self::$dbResults[$queryPattern] = $result;
    }
    
    /**
     * Get recorded errors
     */
    public static function getErrors(): array {
        return self::$errors;
    }
    
    /**
     * Get recorded GL transactions
     */
    public static function getGlTransactions(): array {
        return self::$glTransactions;
    }
    
    /**
     * Clear all mocks
     */
    public static function clear(): void {
        self::$errors = [];
        self::$glTransactions = [];
    }
    
    /**
     * Mock get_company_pref()
     */
    public static function mockGetCompanyPref(string $key) {
        return self::$companyPrefs[$key] ?? null;
    }
    
    /**
     * Mock get_last_exchange_rate()
     */
    public static function mockGetLastExchangeRate(string $currency, string $date) {
        $key = $currency . '_' . $date;
        return self::$exchangeRates[$key] ?? null;
    }
    
    /**
     * Mock display_error()
     */
    public static function mockDisplayError(string $msg, bool $exit = false): void {
        self::$errors[] = [
            'message' => $msg,
            'exit' => $exit
        ];
    }
    
    /**
     * Mock db_query()
     */
    public static function mockDbQuery(string $sql, ?string $errMsg = null) {
        foreach (self::$dbResults as $pattern => $result) {
            if (strpos($sql, $pattern) !== false) {
                return $result;
            }
        }
        return ['mock_result' => true];
    }
    
    /**
     * Mock db_fetch_row()
     */
    public static function mockDbFetchRow($result): array {
        if (is_array($result) && isset($result['count'])) {
            return [$result['count']];
        }
        return [0];
    }
    
    /**
     * Mock round2()
     */
    public static function mockRound2(float $value, int $decimals = 2): float {
        return round($value, $decimals);
    }
    
    /**
     * Mock user_price_dec()
     */
    public static function mockUserPriceDec(): int {
        return 2;
    }
    
    /**
     * Mock add_gl_trans()
     */
    public static function mockAddGlTrans(
        int $type, int $transNo, string $date, string $account, 
        int $dim1, int $dim2, string $memo, float $amount, 
        $currency = null, $personType = null, $personId = null
    ): void {
        self::$glTransactions[] = [
            'type' => $type,
            'trans_no' => $transNo,
            'date' => $date,
            'account' => $account,
            'dimension_id' => $dim1,
            'dimension2_id' => $dim2,
            'memo' => $memo,
            'amount' => $amount,
            'currency' => $currency,
            'person_type_id' => $personType,
            'person_id' => $personId
        ];
    }
    
    /**
     * Mock get_customer_trans()
     */
    public static function mockGetCustomerTrans(int $transNo, int $type): array {
        return [
            'trans_no' => $transNo,
            'type' => $type,
            'debtor_no' => 1,
            'branch_code' => 1,
            'curr_code' => 'EUR',
            'rate' => 1.18,
            'tran_date' => '2025-01-01'
        ];
    }
    
    /**
     * Mock get_supp_trans()
     */
    public static function mockGetSuppTrans(int $transNo, int $type): array {
        return [
            'trans_no' => $transNo,
            'type' => $type,
            'supplier_id' => 1,
            'curr_code' => 'EUR',
            'rate' => 1.18,
            'tran_date' => '2025-01-01'
        ];
    }
    
    /**
     * Mock get_branch_accounts()
     */
    public static function mockGetBranchAccounts(int $branchCode): array {
        return [
            'receivables_account' => '1200',
            'sales_account' => '4000',
            'sales_discount_account' => '4100'
        ];
    }
    
    /**
     * Mock get_supplier_accounts()
     */
    public static function mockGetSupplierAccounts(int $supplierId): array {
        return [
            'payable_account' => '2100',
            'purchase_account' => '5000',
            'payment_discount_account' => '5100'
        ];
    }
    
    /**
     * Mock sql2date()
     */
    public static function mockSql2Date(string $sqlDate): string {
        return $sqlDate; // Simplified for testing
    }
    
    /**
     * Mock date1_greater_date2()
     */
    public static function mockDate1GreaterDate2(string $date1, string $date2): bool {
        return strtotime($date1) > strtotime($date2);
    }
}

/**
 * Register global function mocks
 * These functions will be used during testing when the real functions aren't available
 */
if (!function_exists('get_company_pref')) {
    function get_company_pref(string $key) {
        return \FA\Tests\MockFactory::mockGetCompanyPref($key);
    }
}

if (!function_exists('get_last_exchange_rate')) {
    function get_last_exchange_rate(string $currency, string $date) {
        return \FA\Tests\MockFactory::mockGetLastExchangeRate($currency, $date);
    }
}

if (!function_exists('display_error')) {
    function display_error(string $msg, bool $exit = false): void {
        \FA\Tests\MockFactory::mockDisplayError($msg, $exit);
    }
}

if (!function_exists('db_query')) {
    function db_query(string $sql, ?string $errMsg = null) {
        return \FA\Tests\MockFactory::mockDbQuery($sql, $errMsg);
    }
}

if (!function_exists('db_fetch_row')) {
    function db_fetch_row($result): array {
        return \FA\Tests\MockFactory::mockDbFetchRow($result);
    }
}

if (!function_exists('round2')) {
    function round2(float $value, int $decimals = 2): float {
        return \FA\Tests\MockFactory::mockRound2($value, $decimals);
    }
}

if (!function_exists('user_price_dec')) {
    function user_price_dec(): int {
        return \FA\Tests\MockFactory::mockUserPriceDec();
    }
}

if (!function_exists('add_gl_trans')) {
    function add_gl_trans(
        int $type, int $transNo, string $date, string $account, 
        int $dim1, int $dim2, string $memo, float $amount, 
        $currency = null, $personType = null, $personId = null
    ): void {
        \FA\Tests\MockFactory::mockAddGlTrans(
            $type, $transNo, $date, $account, $dim1, $dim2, $memo, 
            $amount, $currency, $personType, $personId
        );
    }
}

if (!function_exists('get_customer_trans')) {
    function get_customer_trans(int $transNo, int $type): array {
        return \FA\Tests\MockFactory::mockGetCustomerTrans($transNo, $type);
    }
}

if (!function_exists('get_supp_trans')) {
    function get_supp_trans(int $transNo, int $type): array {
        return \FA\Tests\MockFactory::mockGetSuppTrans($transNo, $type);
    }
}

if (!function_exists('get_branch_accounts')) {
    function get_branch_accounts(int $branchCode): array {
        return \FA\Tests\MockFactory::mockGetBranchAccounts($branchCode);
    }
}

if (!function_exists('get_supplier_accounts')) {
    function get_supplier_accounts(int $supplierId): array {
        return \FA\Tests\MockFactory::mockGetSupplierAccounts($supplierId);
    }
}

if (!function_exists('sql2date')) {
    function sql2date(string $sqlDate): string {
        return \FA\Tests\MockFactory::mockSql2Date($sqlDate);
    }
}

if (!function_exists('date1_greater_date2')) {
    function date1_greater_date2(string $date1, string $date2): bool {
        return \FA\Tests\MockFactory::mockDate1GreaterDate2($date1, $date2);
    }
}

if (!function_exists('check_empty_result')) {
    function check_empty_result(string $sql): bool {
        $result = db_query($sql);
        $row = db_fetch_row($result);
        return is_array($row) && $row[0] > 0;
    }
}
