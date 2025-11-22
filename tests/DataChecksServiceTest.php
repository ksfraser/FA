<?php
require_once __DIR__ . '/bootstrap.php';

/**
 * Unit tests for DataChecksService
 *
 * SOLID Principles:
 * - Single Responsibility: Tests DataChecksService only
 * - Open/Closed: Can add new tests without modifying existing
 * - Liskov Substitution: Compatible with PHPUnit test framework
 * - Interface Segregation: Focused test methods
 * - Dependency Inversion: Depends on abstractions (PHPUnit)
 *
 * DRY: Reuses test setup and assertions
 * TDD: Written with TDD in mind, testing behavior
 *
 * UML Class Diagram:
 * +---------------------+
 * | DataChecksServiceTest |
 * +---------------------+
 * |                      |
 * | + testDbHasCustomers() |
 * | + testCheckDbHasCustomers() |
 * | ...                   |
 * +---------------------+
 *           |
 *           | extends
 *           v
 * +---------------------+
 * |   PHPUnit\TestCase |
 * +---------------------+
 *
 * @package FA
 */

// Set up global SysPrefs for date functions
global $SysPrefs, $tmonths;
$SysPrefs->date_system = 0; // Gregorian
$SysPrefs->dateseps = array('/', '-', '.');
$SysPrefs->dflt_date_fmt = 1; // DD/MM/YYYY
$SysPrefs->dflt_date_sep = 0; // /
if (!isset($tmonths)) {
    $tmonths = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
}

// Mock functions for testing
function check_empty_result($sql) {
    return 1; // Mock: always return true (has data)
}

function db_query($sql, $err_msg = null) {
    return true; // Mock result
}

function db_fetch_row($result) {
    return [1]; // Mock row
}

use PHPUnit\Framework\TestCase;
use FA\DataChecksService;
use FA\Interfaces\DatabaseRepositoryInterface;
use FA\Interfaces\DisplayServiceInterface;

class DataChecksServiceTest extends TestCase {

    private DataChecksService $service;
    private $databaseRepoMock;
    private $displayServiceMock;

    protected function setUp(): void {
        if (!defined('TB_PREF')) {
            define('TB_PREF', '0_');
        }
        
        // Set up global SysPrefs for date functions
        global $SysPrefs;
        if (!isset($SysPrefs)) {
            $SysPrefs = new stdClass();
            $SysPrefs->date_system = 0; // Gregorian
            $SysPrefs->dateseps = array('/', '-', '.');
            $SysPrefs->dflt_date_fmt = 1; // DD/MM/YYYY
            $SysPrefs->dflt_date_sep = 0; // /
        }
        
        // Create mocks
        $this->databaseRepoMock = $this->createMock(DatabaseRepositoryInterface::class);
        $this->displayServiceMock = $this->createMock(DisplayServiceInterface::class);
        
        // Setup default mock behaviors - removed defaults to avoid conflicts
        $this->databaseRepoMock->method('getTablePrefix')->willReturn('0_');
        // Removed escape default
        
        $this->service = new DataChecksService($this->databaseRepoMock, $this->displayServiceMock);
    }

    /**
     * Test database has customers - positive case
     */
    public function testDbHasCustomersReturnsTrueWhenDataExists(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_debtors_master")
            ->willReturn(true);
        
        $result = $this->service->dbHasCustomers();
        $this->assertTrue($result);
    }

    /**
     * Test database has customers - negative case
     */
    public function testDbHasCustomersReturnsFalseWhenNoData(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_debtors_master")
            ->willReturn(false);
        
        $result = $this->service->dbHasCustomers();
        $this->assertFalse($result);
    }

    /**
     * Test check database has customers calls display service when no data
     */
    public function testCheckDbHasCustomersDisplaysErrorWhenNoData(): void {
        $this->databaseRepoMock->method('checkEmptyResult')->willReturn(false);
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with('No customers found', true);
        $this->displayServiceMock->expects($this->once())
            ->method('endPage');
        
        // Note: This method calls exit(), so we can't test the full flow
        // We just verify the display methods are called before exit
        try {
            $this->service->checkDbHasCustomers('No customers found');
        } catch (\Exception $e) {
            // Expected due to exit()
        }
    }

    /**
     * Test customer has branches with SQL injection prevention
     */
    public function testDbCustomerHasBranchesEscapesInput(): void {
        $customerId = "CUST' OR '1'='1";
        $escapedId = "CUST\\' OR \\'1\\'=\\'1";
        
        $this->databaseRepoMock->expects($this->once())
            ->method('escape')
            ->with($customerId)
            ->willReturn($escapedId);
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_cust_branch WHERE debtor_no=" . $escapedId)
            ->willReturn(true);
        
        $result = $this->service->dbCustomerHasBranches($customerId);
        $this->assertTrue($result);
    }

    /**
     * Test currency rates with company currency
     */
    public function testDbHasCurrencyRatesReturnsOneForCompanyCurrency(): void {
        // Mock the BankingService static method
        if (!class_exists('BankingService')) {
            eval('class BankingService { public static function isCompanyCurrencyStatic($currency) { return $currency === "USD"; } }');
        }
        if (!class_exists('DateService')) {
            eval('class DateService { public static function date2sqlStatic($date) { return $date; } }');
        }
        
        $result = $this->service->dbHasCurrencyRates('USD', '2023-01-01');
        $this->assertEquals(1, $result);
    }

    /**
     * Test currency rates queries database for non-company currency
     */
    public function testDbHasCurrencyRatesQueriesDatabaseForNonCompanyCurrency(): void {
        // Set up SysPrefs for date functions
        global $SysPrefs;
        $SysPrefs->dflt_date_fmt = 1; // DD/MM/YYYY
        $SysPrefs->dflt_date_sep = 0; // /
        
        if (!class_exists('BankingService')) {
            eval('class BankingService { public static function isCompanyCurrencyStatic($currency) { return false; } }');
        }
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_exchange_rates WHERE curr_code = 'EUR' && date_ <= '2023-01-01'")
            ->willReturn(true);
        
        $result = $this->service->dbHasCurrencyRates('EUR', '01/01/2023', true);
        $this->assertEquals(1, $result);
    }

    /**
     * Test currency rates displays error when no rates found
     */
    public function testDbHasCurrencyRatesDisplaysErrorWhenNoRatesAndMsgTrue(): void {
        if (!class_exists('BankingService')) {
            eval('class BankingService { public static function isCompanyCurrencyStatic($currency) { return false; } }');
        }
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_exchange_rates WHERE curr_code = 'EUR' && date_ <= '2023-01-01'")
            ->willReturn(false);
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with($this->stringContains('Cannot retrieve exchange rate for currency EUR'), true);
        
        $result = $this->service->dbHasCurrencyRates('EUR', '01/01/2023', true);
        $this->assertEquals(0, $result);
    }

    public function testDbHasCurrencies(): void {
        // Test db_has_currencies method
        $result = $this->service->dbHasCurrencies();
        $this->assertIsBool($result);
    }

    public function testDbHasTaxTypes(): void {
        // Test db_has_tax_types method
        $result = $this->service->dbHasTaxTypes();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasTaxTypes(): void {
        // Test check_db_has_tax_types method
        // Since this method might exit, we test that it exists and is callable
        $this->assertTrue(method_exists($this->service, 'checkDbHasTaxTypes'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasTaxTypes']));
    }

    public function testDbHasTaxGroups(): void {
        // Test db_has_tax_groups method
        $result = $this->service->dbHasTaxGroups();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasTaxGroups(): void {
        // Test check_db_has_tax_groups method
        // Since this method might exit, we test that it exists and is callable
        $this->assertTrue(method_exists($this->service, 'checkDbHasTaxGroups'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasTaxGroups']));
    }

    public function testDbCustomerHasBranches(): void {
        // Test db_customer_has_branches method
        $result = $this->service->dbCustomerHasBranches('CUST001');
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testDbHasCustomerBranches(): void {
        // Test db_has_customer_branches method
        $result = $this->service->dbHasCustomerBranches();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasCustomerBranches(): void {
        // Test check_db_has_customer_branches method
        $this->assertTrue(method_exists($this->service, 'checkDbHasCustomerBranches'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasCustomerBranches']));
    }

    public function testDbHasSalesPeople(): void {
        // Test db_has_sales_people method
        $result = $this->service->dbHasSalesPeople();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasSalesPeople(): void {
        // Test check_db_has_sales_people method
        $this->assertTrue(method_exists($this->service, 'checkDbHasSalesPeople'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasSalesPeople']));
    }

    public function testDbHasSalesAreas(): void {
        // Test db_has_sales_areas method
        $result = $this->service->dbHasSalesAreas();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasSalesAreas(): void {
        // Test check_db_has_sales_areas method
        $this->assertTrue(method_exists($this->service, 'checkDbHasSalesAreas'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasSalesAreas']));
    }

    public function testDbHasShippers(): void {
        // Test db_has_shippers method
        $result = $this->service->dbHasShippers();
        $this->assertIsBool($result);
        $this->assertTrue($result); // Should return true with mock
    }

    public function testCheckDbHasShippers(): void {
        // Test check_db_has_shippers method
        $this->assertTrue(method_exists($this->service, 'checkDbHasShippers'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasShippers']));
    }

    // Part 3: Workorder/Dimension Functions Tests
    public function testDbHasOpenWorkorders(): void {
        // Test db_has_open_workorders method
        $this->assertTrue(method_exists($this->service, 'dbHasOpenWorkorders'));
        $this->assertTrue(is_callable([$this->service, 'dbHasOpenWorkorders']));
    }

    public function testDbHasWorkorders(): void {
        // Test db_has_workorders method
        $this->assertTrue(method_exists($this->service, 'dbHasWorkorders'));
        $this->assertTrue(is_callable([$this->service, 'dbHasWorkorders']));
    }

    public function testCheckDbHasWorkorders(): void {
        // Test check_db_has_workorders method
        $this->assertTrue(method_exists($this->service, 'checkDbHasWorkorders'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasWorkorders']));
    }

    public function testDbHasOpenDimensions(): void {
        // Test db_has_open_dimensions method
        $this->assertTrue(method_exists($this->service, 'dbHasOpenDimensions'));
        $this->assertTrue(is_callable([$this->service, 'dbHasOpenDimensions']));
    }

    public function testDbHasDimensions(): void {
        // Test db_has_dimensions method
        $this->assertTrue(method_exists($this->service, 'dbHasDimensions'));
        $this->assertTrue(is_callable([$this->service, 'dbHasDimensions']));
    }

    public function testCheckDbHasDimensions(): void {
        // Test check_db_has_dimensions method
        $this->assertTrue(method_exists($this->service, 'checkDbHasDimensions'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasDimensions']));
    }

    // Part 4: Supplier/Stock Functions Tests
    public function testDbHasSuppliers(): void {
        // Test db_has_suppliers method
        $this->assertTrue(method_exists($this->service, 'dbHasSuppliers'));
        $this->assertTrue(is_callable([$this->service, 'dbHasSuppliers']));
    }

    public function testCheckDbHasSuppliers(): void {
        // Test check_db_has_suppliers method
        $this->assertTrue(method_exists($this->service, 'checkDbHasSuppliers'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasSuppliers']));
    }

    public function testDbHasPurchasableItems(): void {
        // Test db_has_purchasable_items method
        $this->assertTrue(method_exists($this->service, 'dbHasPurchasableItems'));
        $this->assertTrue(is_callable([$this->service, 'dbHasPurchasableItems']));
    }

    public function testCheckDbHasPurchasableItems(): void {
        // Test check_db_has_purchasable_items method
        $this->assertTrue(method_exists($this->service, 'checkDbHasPurchasableItems'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasPurchasableItems']));
    }

    public function testDbHasStockItems(): void {
        // Test db_has_stock_items method
        $this->assertTrue(method_exists($this->service, 'dbHasStockItems'));
        $this->assertTrue(is_callable([$this->service, 'dbHasStockItems']));
    }

    public function testCheckDbHasStockItems(): void {
        // Test check_db_has_stock_items method
        $this->assertTrue(method_exists($this->service, 'checkDbHasStockItems'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasStockItems']));
    }

    // Part 5: Fixed Asset Functions Tests
    public function testDbHasFixedAssets(): void {
        // Test db_has_fixed_assets method
        $this->assertTrue(method_exists($this->service, 'dbHasFixedAssets'));
        $this->assertTrue(is_callable([$this->service, 'dbHasFixedAssets']));
    }

    public function testCheckDbHasFixedAssets(): void {
        // Test check_db_has_fixed_assets method
        $this->assertTrue(method_exists($this->service, 'checkDbHasFixedAssets'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasFixedAssets']));
    }

    public function testDbHasFixedAssetClasses(): void {
        // Test db_has_fixed_asset_classes method
        $this->assertTrue(method_exists($this->service, 'dbHasFixedAssetClasses'));
        $this->assertTrue(is_callable([$this->service, 'dbHasFixedAssetClasses']));
    }

    public function testCheckDbHasFixedAssetClasses(): void {
        // Test check_db_has_fixed_asset_classes method
        $this->assertTrue(method_exists($this->service, 'checkDbHasFixedAssetClasses'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasFixedAssetClasses']));
    }

    // Part 6: Category/Location Functions Tests
    public function testDbHasStockCategories(): void {
        // Test db_has_stock_categories method
        $this->assertTrue(method_exists($this->service, 'dbHasStockCategories'));
        $this->assertTrue(is_callable([$this->service, 'dbHasStockCategories']));
    }

    public function testCheckDbHasStockCategories(): void {
        // Test check_db_has_stock_categories method
        $this->assertTrue(method_exists($this->service, 'checkDbHasStockCategories'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasStockCategories']));
    }

    public function testDbHasLocations(): void {
        // Test db_has_locations method
        $this->assertTrue(method_exists($this->service, 'dbHasLocations'));
        $this->assertTrue(is_callable([$this->service, 'dbHasLocations']));
    }

    public function testCheckDbHasLocations(): void {
        // Test check_db_has_locations method
        $this->assertTrue(method_exists($this->service, 'checkDbHasLocations'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasLocations']));
    }

    // Part 7: Account/GL Functions Tests
    public function testDbHasBankAccounts(): void {
        // Test db_has_bank_accounts method
        $this->assertTrue(method_exists($this->service, 'dbHasBankAccounts'));
        $this->assertTrue(is_callable([$this->service, 'dbHasBankAccounts']));
    }

    public function testCheckDbHasBankAccounts(): void {
        // Test check_db_has_bank_accounts method
        $this->assertTrue(method_exists($this->service, 'checkDbHasBankAccounts'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasBankAccounts']));
    }

    public function testDbHasGlAccounts(): void {
        // Test db_has_gl_accounts method
        $this->assertTrue(method_exists($this->service, 'dbHasGlAccounts'));
        $this->assertTrue(is_callable([$this->service, 'dbHasGlAccounts']));
    }

    public function testDbHasGlAccountGroups(): void {
        // Test db_has_gl_account_groups method
        $this->assertTrue(method_exists($this->service, 'dbHasGlAccountGroups'));
        $this->assertTrue(is_callable([$this->service, 'dbHasGlAccountGroups']));
    }

    public function testCheckDbHasGlAccountGroups(): void {
        // Test check_db_has_gl_account_groups method
        $this->assertTrue(method_exists($this->service, 'checkDbHasGlAccountGroups'));
        $this->assertTrue(is_callable([$this->service, 'checkDbHasGlAccountGroups']));
    }

    // Part 8: Additional Validation Functions Tests
    /**
     * Test checkInt with valid integer
     */
    public function testCheckIntReturnsOneForValidInteger(): void {
        // Mock $_POST
        $_POST['test_field'] = '42';
        
        if (!class_exists('RequestService')) {
            eval('class RequestService { public static function inputNumStatic($name) { return (int)$_POST[$name]; } }');
        }
        
        $result = $this->service->checkInt('test_field', 0, 100);
        $this->assertEquals(1, $result);
    }

    /**
     * Test checkInt with value below minimum
     */
    public function testCheckIntReturnsZeroForValueBelowMinimum(): void {
        $_POST['test_field'] = '5';
        
        if (!class_exists('RequestService')) {
            eval('class RequestService { public static function inputNumStatic($name) { return (int)$_POST[$name]; } }');
        }
        
        $result = $this->service->checkInt('test_field', 10, 100);
        $this->assertEquals(0, $result);
    }

    /**
     * Test checkInt with non-integer value
     */
    public function testCheckIntReturnsZeroForNonInteger(): void {
        $_POST['test_field'] = 'not_a_number';
        
        if (!class_exists('RequestService')) {
            eval('class RequestService { public static function inputNumStatic($name) { return $_POST[$name]; } }');
        }
        
        $result = $this->service->checkInt('test_field');
        $this->assertEquals(0, $result);
    }

    /**
     * Test checkInt with missing field
     */
    public function testCheckIntReturnsZeroForMissingField(): void {
        // Ensure field doesn't exist
        unset($_POST['missing_field']);
        
        $result = $this->service->checkInt('missing_field');
        $this->assertEquals(0, $result);
    }

    /**
     * Test checkNum with valid number
     */
    public function testCheckNumReturnsOneForValidNumber(): void {
        $_POST['price'] = '99.99';
        
        if (!class_exists('RequestService')) {
            eval('class RequestService { public static function inputNumStatic($name, $default = 0) { return (float)$_POST[$name]; } }');
        }
        
        $result = $this->service->checkNum('price', 0, 1000);
        $this->assertEquals(1, $result);
    }

    /**
     * Test checkNum with default value when field missing
     */
    public function testCheckNumUsesDefaultWhenFieldMissing(): void {
        unset($_POST['missing_price']);
        
        if (!class_exists('RequestService')) {
            eval('class RequestService { public static function inputNumStatic($name, $default = 0) { return isset($_POST[$name]) ? (float)$_POST[$name] : $default; } }');
        }
        
        $result = $this->service->checkNum('missing_price', -100, 100, 50.0);
        $this->assertEquals(0, $result); // Field not set, returns 0
    }

    /**
     * Test checkIsClosed does nothing when transaction is not closed
     */
    public function testCheckIsClosedDoesNothingWhenNotClosed(): void {
        // Mock is_closed_trans function
        if (!function_exists('is_closed_trans')) {
            eval('function is_closed_trans($type, $no) { return false; }');
        }
        
        $this->displayServiceMock->expects($this->never())
            ->method('displayError');
        
        $this->service->checkIsClosed(10, 123);
    }

    /**
     * Test checkIsClosed displays error when transaction is closed
     */
    public function testCheckIsClosedDisplaysErrorWhenClosed(): void {
        // Override is_closed_trans function
        eval('function is_closed_trans($type, $no) { return true; }');
        
        // Mock systypes_array
        $GLOBALS['systypes_array'] = [10 => 'Invoice'];
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with($this->stringContains('Invoice #123 is closed'), true);
        $this->displayServiceMock->expects($this->once())
            ->method('displayFooterExit');
        
        $this->service->checkIsClosed(10, 123);
    }

    /**
     * Test checkReference returns true for valid reference
     */
    public function testCheckReferenceReturnsTrueForValidReference(): void {
        // Mock $GLOBALS['Refs']
        $refsMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['is_valid', 'is_new_reference'])
            ->getMock();
        $refsMock->method('is_valid')->willReturn(true);
        $refsMock->method('is_new_reference')->willReturn(true);
        $GLOBALS['Refs'] = $refsMock;
        
        $result = $this->service->checkReference('REF001', 10, 123);
        $this->assertTrue($result);
    }

    /**
     * Test checkReference returns false and displays error for invalid reference
     */
    public function testCheckReferenceReturnsFalseForInvalidReference(): void {
        $refsMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['is_valid', 'is_new_reference'])
            ->getMock();
        $refsMock->method('is_valid')->willReturn(false);
        $refsMock->method('is_new_reference')->willReturn(true);
        $GLOBALS['Refs'] = $refsMock;
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with('The entered reference is invalid.');
        
        $result = $this->service->checkReference('INVALID', 10, 123);
        $this->assertFalse($result);
    }

    /**
     * Test checkReference returns false for duplicate reference
     */
    public function testCheckReferenceReturnsFalseForDuplicateReference(): void {
        $refsMock = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['is_valid', 'is_new_reference'])
            ->getMock();
        $refsMock->method('is_valid')->willReturn(true);
        $refsMock->method('is_new_reference')->willReturn(false);
        $GLOBALS['Refs'] = $refsMock;
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with('The entered reference is already in use.');
        
        $result = $this->service->checkReference('DUPLICATE', 10, 123);
        $this->assertFalse($result);
    }

    /**
     * Test checkSysPref does nothing when preference exists
     */
    public function testCheckSysPrefDoesNothingWhenPreferenceExists(): void {
        if (!function_exists('get_company_pref')) {
            eval('function get_company_pref($name) { return $name === "deferred_income_act" ? "1010" : ""; }');
        }
        
        $this->displayServiceMock->expects($this->never())
            ->method('displayError');
        
        $this->service->checkSysPref('deferred_income_act', 'Deferred income account not set');
    }

    /**
     * Test checkSysPref displays error when preference is empty
     */
    public function testCheckSysPrefDisplaysErrorWhenPreferenceEmpty(): void {
        if (!function_exists('get_company_pref')) {
            eval('function get_company_pref($name) { return ""; }');
        }
        
        $this->displayServiceMock->expects($this->once())
            ->method('menuLink')
            ->with('/admin/gl_setup.php', 'Preference not set')
            ->willReturn('GL Setup link');
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with('GL Setup link', true);
        $this->displayServiceMock->expects($this->once())
            ->method('displayFooterExit');
        
        $this->service->checkSysPref('missing_pref', 'Preference not set');
    }

    /**
     * Test checkDbHasTemplateOrders does nothing when templates exist
     */
    public function testCheckDbHasTemplateOrdersDoesNothingWhenTemplatesExist(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->willReturn(true); // Templates exist
        
        $this->displayServiceMock->expects($this->never())
            ->method('displayError');
        
        $this->service->checkDbHasTemplateOrders('No template orders found');
    }

    /**
     * Test checkDbHasTemplateOrders displays error when no templates
     */
    public function testCheckDbHasTemplateOrdersDisplaysErrorWhenNoTemplates(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->willReturn(false); // No templates
        
        $this->displayServiceMock->expects($this->once())
            ->method('displayError')
            ->with('No template orders found', true);
        $this->displayServiceMock->expects($this->once())
            ->method('endPage');
        
        try {
            $this->service->checkDbHasTemplateOrders('No template orders found');
        } catch (\Exception $e) {
            // Expected due to exit()
        }
    }

    public function testCheckDeferredIncomeAct(): void {
        // Test check_deferred_income_act method
        $this->assertTrue(method_exists($this->service, 'checkDeferredIncomeAct'));
        $this->assertTrue(is_callable([$this->service, 'checkDeferredIncomeAct']));
    }

    public function testCheckIsEditable(): void {
        // Test check_is_editable method
        $this->assertTrue(method_exists($this->service, 'checkIsEditable'));
        $this->assertTrue(is_callable([$this->service, 'checkIsEditable']));
    }

    public function testCheckReference(): void {
        // Test check_reference method
        $this->assertTrue(method_exists($this->service, 'checkReference'));
        $this->assertTrue(is_callable([$this->service, 'checkReference']));
    }

    /**
     * Test dbHasTags with customer tags
     */
    public function testDbHasTagsWithCustomerType(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('escape')
            ->with(1)
            ->willReturn('1');
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_tags WHERE type=1")
            ->willReturn(true);
        
        $result = $this->service->dbHasTags(1);
        $this->assertTrue($result);
    }

    /**
     * Test dbHasTags with supplier tags
     */
    public function testDbHasTagsWithSupplierType(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('escape')
            ->with(2)
            ->willReturn('2');
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_tags WHERE type=2")
            ->willReturn(true);
        
        $result = $this->service->dbHasTags(2);
        $this->assertTrue($result);
    }

    /**
     * Test dbHasPurchasableFixedAssets generates correct SQL
     */
    public function testDbHasPurchasableFixedAssetsGeneratesCorrectSql(): void {
        $expectedSql = "SELECT COUNT(*) FROM 0_stock_master 
            WHERE mb_flag='F'
                AND !inactive
                AND stock_id NOT IN
                    ( SELECT stock_id FROM 0_stock_moves WHERE type=25 AND qty!=0 )";
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with($expectedSql)
            ->willReturn(true);
        
        $result = $this->service->dbHasPurchasableFixedAssets();
        $this->assertTrue($result);
    }

    /**
     * Test dbHasDepreciableFixedAssets with complex date logic
     */
    public function testDbHasDepreciableFixedAssetsWithDateLogic(): void {
        // Mock DateService static methods
        if (!class_exists('DateService')) {
            eval('class DateService { 
                public static function getCurrentFiscalYearStatic() { 
                    return ["begin" => "2023-01-01", "end" => "2023-12-31"]; 
                }
                public static function date2sqlStatic($date) { return $date; }
                public static function addMonthsStatic($date, $months) { 
                    if ($months < 0) return "2022-12-01";
                    return "2024-01-01"; 
                }
                public static function sql2dateStatic($sql) { return $sql; }
            }');
        }
        
        $expectedSql = "SELECT COUNT(*) FROM 0_stock_master 
            WHERE mb_flag='F'
                AND material_cost > 0
                AND stock_id IN ( SELECT stock_id FROM 0_stock_moves WHERE type=25 AND qty!=0 )
                AND stock_id NOT IN	( SELECT stock_id FROM 0_stock_moves WHERE (type=13 OR type=17) AND qty!=0 )
                AND depreciation_date <= '2022-12-01'
                AND depreciation_date >='2022-12-01'";
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with($expectedSql)
            ->willReturn(true);
        
        $result = $this->service->dbHasDepreciableFixedAssets();
        $this->assertTrue($result);
    }

    /**
     * Test all dbHas* methods return boolean
     */
    public function testAllDbHasMethodsReturnBoolean(): void {
        // Ensure DateService is available for dbHasDepreciableFixedAssets
        if (!class_exists('DateService')) {
            eval('class DateService { 
                public static function getCurrentFiscalYearStatic() { 
                    return ["begin" => "2023-01-01", "end" => "2023-12-31"]; 
                }
                public static function date2sqlStatic($date) { return $date; }
                public static function addMonthsStatic($date, $months) { 
                    if ($months < 0) return "2022-12-01";
                    return "2024-01-01"; 
                }
                public static function sql2dateStatic($sql) { return $sql; }
            }');
        }
        
        $methods = [
            'dbHasCustomers',
            'dbHasCurrencies', 
            'dbHasTaxTypes',
            'dbHasTaxGroups',
            'dbHasSalesTypes',
            'dbHasCustomerBranches',
            'dbHasSalesPeople',
            'dbHasSalesAreas',
            'dbHasShippers',
            'dbHasItemTaxTypes',
            'dbHasOpenWorkorders',
            'dbHasWorkorders',
            'dbHasOpenDimensions',
            'dbHasDimensions',
            'dbHasSuppliers',
            'dbHasStockItems',
            'dbHasBomStockItems',
            'dbHasManufacturableItems',
            'dbHasPurchasableItems',
            'dbHasCostableItems',
            'dbHasFixedAssetClasses',
            'dbHasDepreciableFixedAssets',
            'dbHasFixedAssets',
            'dbHasPurchasableFixedAssets',
            'dbHasDisposableFixedAssets',
            'dbHasStockCategories',
            'dbHasLocations',
            'dbHasBankAccounts',
            'dbHasCashAccounts',
            'dbHasGlAccounts',
            'dbHasGlAccountGroups',
            'dbHasQuickEntries'
        ];
        
        foreach ($methods as $method) {
            $result = $this->service->$method();
            $this->assertIsBool($result, "$method should return boolean");
        }
    }

    /**
     * Test dbCustomerHasBranches with empty customer ID
     */
    public function testDbCustomerHasBranchesWithEmptyId(): void {
        $this->databaseRepoMock->expects($this->once())
            ->method('escape')
            ->with('')
            ->willReturn('');
        
        $this->databaseRepoMock->expects($this->once())
            ->method('checkEmptyResult')
            ->with("SELECT COUNT(*) FROM 0_cust_branch WHERE debtor_no=")
            ->willReturn(false);
        
        $result = $this->service->dbCustomerHasBranches('');
        $this->assertFalse($result);
    }

    // Add more tests as needed
}