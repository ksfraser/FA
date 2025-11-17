# 🚀 Migration Guide: Legacy to Modern OOP

## Overview

This guide helps developers migrate existing FrontAccounting code from procedural style to the new OOP architecture with dependency injection.

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Service Usage](#service-usage)
3. [Dependency Injection](#dependency-injection)
4. [Testing Your Code](#testing-your-code)
5. [Common Patterns](#common-patterns)
6. [Database Abstraction](#database-abstraction)
7. [Best Practices](#best-practices)

---

## Quick Start

### Before (Procedural)
```php
// Old way - direct function calls
$rate = get_exchange_rate_from_to($currency, $date);
$formatted = __date(2024, 11, 17);

if (!db_has_customers()) {
    display_error("No customers found");
}
```

### After (OOP with Services)
```php
// New way - use services
$bankingService = new \FA\BankingService();
$rate = $bankingService->getExchangeRateFromTo($currency, $date);

$dateService = new \FA\DateService();
$formatted = $dateService->formatDate(2024, 11, 17);

$dataChecks = new \FA\DataChecksFacade();
$dataChecks->checkDbHasCustomers("No customers found");
```

---

## Service Usage

### Available Services

#### 1. BankingService
```php
use FA\BankingService;

$banking = new BankingService();

// Currency operations
$rate = $banking->getExchangeRate('USD', '2024-11-17');
$converted = $banking->exchangeFromTo($amount, 'EUR', 'USD', '2024-11-17');

// Company preferences
$decimals = $banking->priceDecimalPlaces();
$symbol = $banking->homeCurrencySymbol();
```

#### 2. DateService
```php
use FA\DateService;

$dates = new DateService();

// Date validation and formatting
$isValid = $dates->isDate('2024-11-17');
$today = $dates->today();
$formatted = $dates->formatDate(2024, 11, 17);

// Date arithmetic
$futureDate = $dates->addDays($today, 30);
$pastDate = $dates->addMonths($today, -3);

// Fiscal year operations
$inFiscalYear = $dates->isDateInFiscalYear('2024-11-17');
$isClosed = $dates->isDateClosed('2024-01-15');

// Calendar conversions
list($y, $m, $d) = $dates->gregorianToJalali(2024, 11, 17);
list($y, $m, $d) = $dates->gregorianToIslamic(2024, 11, 17);
```

#### 3. InventoryService
```php
use FA\InventoryService;

$inventory = new InventoryService();

// Item type checking
if ($inventory->isManufactured($mbFlag)) {
    // Handle manufactured item
}

if ($inventory->hasStockHolding($mbFlag)) {
    // Check stock levels
}
```

#### 4. DataChecks (Validation)
```php
use FA\DataChecksFacade;

$checks = new DataChecksFacade();

// Database existence checks
$checks->checkDbHasCustomers("Please add customers first");
$checks->checkDbHasCurrencies("Please configure currencies");

// Input validation
$checks->checkInt('quantity', 1, 9999);
$checks->checkNum('price', 0.01, 999999.99);

// Transaction validation
$checks->checkIsClosed(ST_SALESINVOICE, $transNo, "Invoice is closed");
$checks->checkIsEditable(ST_SALESORDER, $orderId, "Order cannot be edited");
```

#### 5. ErrorsService
```php
use FA\ErrorsService;

$errors = new ErrorsService();

// Error handling
$errors->triggerError('Invalid data', E_USER_WARNING);
$errors->displayDbError('Database operation failed');

// Error boxes
$errors->errorBox('Please correct the following errors');
```

#### 6. TaxCalculationService
```php
use FA\TaxCalculationService;

$tax = new TaxCalculationService();

// Tax calculations
$taxIncluded = $tax->getTaxIncluded($items, $taxGroupId);
$netPrice = $tax->getPrice($grossPrice, $taxGroupId);
```

---

## Dependency Injection

### Basic DI (Optional Parameters)

All services support optional dependency injection while maintaining backward compatibility:

```php
// Default (uses production implementations)
$service = new DateService();

// With custom dependencies
$customFiscalYear = new CustomFiscalYearRepository();
$customCalendar = new CustomCalendarConverter();
$service = new DateService($customFiscalYear, $customCalendar);
```

### Testing with Mocks

```php
use FA\DateService;
use FA\Tests\Mocks\MockFiscalYearRepository;
use FA\Tests\Mocks\MockCalendarConverter;

// Set up mocks
$mockFiscalYear = new MockFiscalYearRepository();
$mockFiscalYear->setFiscalYear([
    'begin' => '2024-01-01',
    'end' => '2024-12-31'
]);

$mockCalendar = new MockCalendarConverter();

// Inject mocks for testing
$service = new DateService($mockFiscalYear, $mockCalendar);

// Test with predictable behavior
$result = $service->gregorianToJalali(2024, 11, 17);
// Result is predictable because we control the mock
```

### Available Interfaces

| Service | Interface | Mock Available |
|---------|-----------|----------------|
| BankingService | CompanyPreferencesInterface<br>ExchangeRateRepositoryInterface<br>DisplayServiceInterface<br>MathServiceInterface | ✅ Yes |
| DateService | FiscalYearRepositoryInterface<br>CalendarConverterInterface | ✅ Yes |
| AccessLevelsService | SecurityRepositoryInterface | ✅ Yes |
| InventoryService | ItemRepositoryInterface | ✅ Yes |
| SalesDbService | SalesRepositoryInterface | ✅ Yes |
| PurchasingDbService | PurchasingRepositoryInterface | ✅ Yes |
| InventoryDbService | InventoryRepositoryInterface | ✅ Yes |
| DataChecks | DatabaseQueryInterface<br>ValidationErrorHandlerInterface | ✅ Yes |

---

## Testing Your Code

### Writing Unit Tests

```php
namespace FA\Tests;

use PHPUnit\Framework\TestCase;
use FA\DateService;
use FA\Tests\Mocks\MockFiscalYearRepository;

class MyCustomTest extends TestCase
{
    private DateService $dateService;
    private MockFiscalYearRepository $fiscalRepo;

    protected function setUp(): void
    {
        $this->fiscalRepo = new MockFiscalYearRepository();
        $this->dateService = new DateService($this->fiscalRepo);
    }

    /** @test */
    public function testMyFeature(): void
    {
        // Arrange
        $this->fiscalRepo->setFiscalYear([
            'begin' => '2024-01-01',
            'end' => '2024-12-31'
        ]);

        // Act
        $result = $this->dateService->isDateInFiscalYear('2024-06-15');

        // Assert
        $this->assertTrue($result);
    }
}
```

### Running Tests

```bash
# Run all tests
php vendor/bin/phpunit

# Run specific test file
php vendor/bin/phpunit tests/MyCustomTest.php

# Run with coverage
php vendor/bin/phpunit --coverage-html coverage/

# Use test runner script
php run-tests.php
```

---

## Common Patterns

### Pattern 1: Service Instantiation

```php
// Singleton-like usage (create once, use everywhere)
class MyController {
    private static ?BankingService $bankingService = null;
    
    protected function getBankingService(): BankingService {
        if (self::$bankingService === null) {
            self::$bankingService = new BankingService();
        }
        return self::$bankingService;
    }
}
```

### Pattern 2: Service Composition

```php
class OrderProcessor {
    private BankingService $banking;
    private DateService $dates;
    private InventoryService $inventory;
    
    public function __construct(
        ?BankingService $banking = null,
        ?DateService $dates = null,
        ?InventoryService $inventory = null
    ) {
        $this->banking = $banking ?? new BankingService();
        $this->dates = $dates ?? new DateService();
        $this->inventory = $inventory ?? new InventoryService();
    }
    
    public function processOrder(array $order): bool {
        // Use services together
        $rate = $this->banking->getExchangeRate($order['currency']);
        $isValid = $this->dates->isDate($order['date']);
        $hasStock = $this->inventory->hasStockHolding($order['mb_flag']);
        
        return $isValid && $hasStock;
    }
}
```

### Pattern 3: Gradual Migration

```php
// Step 1: Keep old function, delegate to new service
function get_exchange_rate_from_to($from, $to, $date) {
    static $service = null;
    if ($service === null) {
        $service = new \FA\BankingService();
    }
    return $service->getExchangeRateFromTo($from, $to, $date);
}

// Step 2: Update callers gradually
// Old: $rate = get_exchange_rate_from_to('USD', 'EUR', $date);
// New: $rate = $banking->getExchangeRateFromTo('USD', 'EUR', $date);

// Step 3: Remove old function when all callers updated
```

---

## Database Abstraction

### New Database Interface

```php
use FA\Interfaces\DatabaseConnectionInterface;
use FA\ProductionDatabaseConnection;

// Get database connection
$db = new ProductionDatabaseConnection();

// Execute query
$result = $db->query("SELECT * FROM customers WHERE id = " . $db->escape($id));

// Fetch results
while ($row = $db->fetch($result)) {
    echo $row['name'];
}

// Or fetch all at once
$rows = $db->fetchAll($result);

// Transactions
$db->begin();
try {
    $db->query("INSERT INTO...");
    $db->query("UPDATE...");
    $db->commit();
} catch (Exception $e) {
    $db->rollback();
    throw $e;
}
```

### Testing with Mock Database

```php
use FA\Tests\Mocks\MockDatabaseConnection;

$db = new MockDatabaseConnection();

// Set expected results
$db->setQueryResult('SELECT * FROM customers', [
    ['id' => 1, 'name' => 'Customer 1'],
    ['id' => 2, 'name' => 'Customer 2']
]);

// Run code under test
$result = $db->query('SELECT * FROM customers');
$rows = $db->fetchAll($result);

// Verify
$this->assertCount(2, $rows);

// Check query log
$queries = $db->getQueryLog();
$this->assertContains('SELECT * FROM customers', $queries);
```

---

## Best Practices

### 1. Use Services Instead of Global Functions ✅

```php
// ❌ Old way
$rate = get_exchange_rate('USD');

// ✅ New way
$banking = new BankingService();
$rate = $banking->getExchangeRate('USD');
```

### 2. Inject Dependencies for Testability ✅

```php
// ❌ Hard to test
class OrderProcessor {
    public function process($order) {
        $rate = get_exchange_rate($order['currency']); // Can't mock
    }
}

// ✅ Easy to test
class OrderProcessor {
    private BankingService $banking;
    
    public function __construct(?BankingService $banking = null) {
        $this->banking = $banking ?? new BankingService();
    }
    
    public function process($order) {
        $rate = $this->banking->getExchangeRate($order['currency']); // Can inject mock
    }
}
```

### 3. Use Type Hints ✅

```php
// ❌ No type safety
function calculateTotal($items, $taxRate) {
    return $items * $taxRate;
}

// ✅ Type safe
function calculateTotal(array $items, float $taxRate): float {
    return array_sum($items) * $taxRate;
}
```

### 4. Follow SOLID Principles ✅

```php
// Single Responsibility - each class does ONE thing
class OrderValidator {
    public function validate(array $order): bool { /* ... */ }
}

class OrderProcessor {
    public function process(array $order): void { /* ... */ }
}

class OrderPersister {
    public function save(array $order): void { /* ... */ }
}
```

### 5. Write Tests ✅

```php
// Every service method should have tests
class DateServiceTest extends TestCase {
    /** @test */
    public function testTodayReturnsCurrentDate(): void {
        $service = new DateService();
        $result = $service->today();
        $this->assertIsString($result);
    }
}
```

---

## Migration Checklist

- [ ] Identify procedural functions in your code
- [ ] Find equivalent service methods (see mapping below)
- [ ] Update function calls to service method calls
- [ ] Add type hints to function parameters
- [ ] Write unit tests for new code
- [ ] Test thoroughly in development
- [ ] Deploy to staging
- [ ] Monitor for issues
- [ ] Deploy to production

---

## Function → Service Mapping

### Banking Functions
| Old Function | New Method | Service |
|--------------|------------|---------|
| `get_exchange_rate()` | `getExchangeRate()` | BankingService |
| `get_exchange_rate_from_to()` | `getExchangeRateFromTo()` | BankingService |
| `exchange_variation()` | `exchangeVariation()` | BankingService |
| `price_decimal_places()` | `priceDecimalPlaces()` | BankingService |

### Date Functions
| Old Function | New Method | Service |
|--------------|------------|---------|
| `__date()` | `formatDate()` | DateService |
| `is_date()` | `isDate()` | DateService |
| `Today()` | `today()` | DateService |
| `Now()` | `now()` | DateService |
| `add_days()` | `addDays()` | DateService |
| `add_months()` | `addMonths()` | DateService |
| `begin_fiscalyear()` | `beginFiscalYear()` | DateService |
| `end_fiscalyear()` | `endFiscalYear()` | DateService |

### Inventory Functions
| Old Function | New Method | Service |
|--------------|------------|---------|
| `is_manufactured()` | `isManufactured()` | InventoryService |
| `is_purchased()` | `isPurchased()` | InventoryService |
| `is_service()` | `isService()` | InventoryService |
| `has_stock_holding()` | `hasStockHolding()` | InventoryService |

### Data Validation
| Old Function | New Method | Service |
|--------------|------------|---------|
| `db_has_customers()` | `dbHasCustomers()` | DataChecksFacade |
| `check_db_has_customers()` | `checkDbHasCustomers()` | DataChecksFacade |
| `check_int()` | `checkInt()` | DataChecksFacade |
| `check_num()` | `checkNum()` | DataChecksFacade |

---

## Support & Questions

- **Documentation**: See `docs/` folder for detailed guides
- **Examples**: Check `tests/` folder for usage examples
- **Issues**: Report bugs via GitHub issues
- **Questions**: Post in discussion forums

---

## Summary

The new OOP architecture provides:
- ✅ Better code organization
- ✅ Full testability
- ✅ Type safety
- ✅ Easier maintenance
- ✅ Future-proof design
- ✅ Backward compatibility

Start migrating your code today! 🚀
