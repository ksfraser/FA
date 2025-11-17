# Legacy Code Replacement Strategy

## Overview
This document outlines the systematic approach to replacing legacy procedural function calls with modern service-based architecture.

## Replacement Phases

### Phase 1: High-Impact, Low-Risk Files (Priority 1)
**Target**: Isolated business logic files with clear service boundaries

#### 1.1 Banking Functions → BankingService
**Legacy Functions**:
- `add_bank_trans()` → `BankingService::addBankTrans()`
- `add_gl_trans_bank()` → `BankingService::addGlTransBank()`
- `add_gl_trans()` → `BankingService::addGlTrans()`
- `get_exchange_rate_from_home_currency()` → `BankingService::getExchangeRateFromHomeCurrency()`
- `get_exchange_rate_to_home_currency()` → `BankingService::getExchangeRateToHomeCurrency()`

**Files to Update**:
- `sales/sales_order_entry.php` (line 558) ✓ FOUND
- `gl/gl_bank.php`
- `purchasing/*.php`
- Other files with banking transactions

#### 1.2 Date Functions → DateService
**Legacy Functions**:
- `is_date()` → `DateService::isDate()`
- `add_days()` → `DateService::addDays()`
- `end_month()` → `DateService::endMonth()`
- `begin_month()` → `DateService::beginMonth()`
- `date_diff2()` → `DateService::dateDiff()`

**Files to Update**:
- Date validation in forms
- Report date range calculations
- Fiscal year calculations

#### 1.3 Inventory Functions → InventoryService
**Legacy Functions**:
- `is_manufactured()` → `InventoryService::isManufactured()`
- `is_purchased()` → `InventoryService::isPurchased()`
- `is_service()` → `InventoryService::isService()`
- `has_stock_holding()` → `InventoryService::hasStockHolding()`

**Files to Update**:
- `inventory/*.php`
- `manufacturing/*.php`
- `sales/*.php` (stock validation)

#### 1.4 Tax Functions → TaxCalculationService
**Legacy Functions**:
- `get_tax_code_items()` → `TaxCalculationService::getTaxCodeItems()`
- `get_tax_code_items_for_item()` → `TaxCalculationService::getTaxCodeItemsForItem()`
- `calculate_tax()` → `TaxCalculationService::calculateTax()`
- `reverse_charge_tax()` → `TaxCalculationService::reverseChargeTax()`

**Files to Update**:
- `sales/sales_order_entry.php`
- `purchasing/po_entry_items.php`
- Tax calculation throughout

### Phase 2: Report Files (Priority 2)
**Target**: Reporting files (less risk, isolated)

**Files to Update**:
- `reporting/rep*.php` files
- Date formatting and calculations
- Data retrieval using repositories

### Phase 3: UI/Form Files (Priority 3)
**Target**: User interface files with validation

**Files to Update**:
- Form validation (is_date, etc.)
- Display formatting
- User input processing

### Phase 4: Core Business Logic (Priority 4)
**Target**: Transaction processing files (highest impact)

**Files to Update**:
- `sales/includes/db/sales_order_db.inc`
- `purchasing/includes/db/po_db.inc`
- `gl/includes/db/gl_db_bank_trans.inc`

## Replacement Pattern

### Standard Replacement
```php
// BEFORE (Legacy)
$rate = get_exchange_rate_from_home_currency($currency, $date);

// AFTER (Service)
$bankingService = new BankingService();
$rate = $bankingService->getExchangeRateFromHomeCurrency($currency, $date);
```

### With Dependency Injection (Preferred)
```php
// At top of file
use FA\Services\BankingService;

// In class constructor or function
$bankingService = new BankingService();

// Usage
$rate = $bankingService->getExchangeRateFromHomeCurrency($currency, $date);
```

### For Include Files
```php
// Add at top after includes
require_once($path_to_root . "/includes/Services/BankingService.php");
use FA\Services\BankingService;

// Create service instance once
$bankingService = new BankingService();

// Use throughout file
$rate = $bankingService->getExchangeRateFromHomeCurrency($currency, $date);
```

## Safety Guidelines

### 1. Test After Each File
- Run the affected page/feature
- Check for PHP errors
- Verify business logic still works

### 2. One Service at a Time
- Complete all replacements for one service before moving to next
- Easier to track issues
- Smaller, manageable commits

### 3. Keep Commits Small
```bash
git add <modified_file>
git commit -m "Replace get_exchange_rate calls with BankingService in sales_order_entry.php"
```

### 4. Document Issues
- Note any unexpected behavior
- Track performance changes
- Keep rollback plan ready

## Rollback Strategy

If issues arise:
```bash
# Revert last commit
git reset --soft HEAD~1

# Or revert specific file
git checkout HEAD -- <file_path>

# Or abandon branch and restart
git checkout 2.4.19
git branch -D refactor/replace-legacy-calls
```

## Progress Tracking

### Phase 1 Progress
- [ ] sales/sales_order_entry.php (BankingService - 1 occurrence)
- [ ] Other banking function calls
- [ ] Date function calls
- [ ] Inventory function calls
- [ ] Tax function calls

### Metrics
- **Files Modified**: 0
- **Legacy Calls Replaced**: 0
- **Services in Use**: 0/12
- **Tests Run**: 0
- **Issues Found**: 0

## Next Steps

1. ✅ Create new branch: `refactor/replace-legacy-calls`
2. ⏳ Replace first occurrence in `sales/sales_order_entry.php`
3. ⏳ Test the change
4. ⏳ Commit if successful
5. ⏳ Find next occurrence
6. ⏳ Repeat

---

**Status**: Ready to begin Phase 1.1 - Banking Functions
**Current File**: sales/sales_order_entry.php (line 558)
**Action**: Replace `get_exchange_rate_from_home_currency()` with `BankingService::getExchangeRateFromHomeCurrency()`
