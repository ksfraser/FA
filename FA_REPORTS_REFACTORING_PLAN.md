# FrontAccounting Built-In Reports Refactoring Plan

## Executive Summary

This document outlines the strategy to refactor FrontAccounting's 50+ legacy procedural reports (rep*.php) into modern, service-oriented architecture following the patterns established in the WebERP-inspired Reports module.

## Current State Analysis

### Report Categories & Inventory

#### Customer Reports (RC_CUSTOMER) - 14 Reports
- **101**: Customer Balances - Balance listing with date range
- **102**: Aged Customer Analysis - Aging buckets with graphics
- **115**: Customer Trial Balance - Period comparison
- **103**: Customer Detail Listing - Activity-based filtering
- **114**: Sales Summary Report - Tax reporting
- **104**: Price Listing - Product pricing by sales type
- **105**: Order Status Listing - Back orders tracking
- **106**: Salesman Listing - Sales rep performance
- **107**: Print Invoices - Transaction printing
- **113**: Print Credit Notes - Credit memo printing
- **110**: Print Deliveries - Packing slips
- **108**: Print Statements - Customer statements
- **109**: Print Sales Orders - Order confirmation
- **111**: Print Sales Quotations - Quote printing
- **112**: Print Receipts - Payment receipts

#### Supplier Reports (RC_SUPPLIER) - 9 Reports
- **201**: Supplier Balances - Balance listing
- **202**: Aged Supplier Analysis - Aging buckets
- **206**: Supplier Trial Balances - Period comparison
- **203**: Payment Report - Payment analysis
- **204**: Outstanding GRN Report - Goods received not invoiced
- **205**: Supplier Detail Listing - Activity filtering
- **209**: Print Purchase Orders - PO printing
- **210**: Print Remittances - Payment remittance advice

#### Inventory Reports (RC_INVENTORY) - 10 Reports
- **301**: Inventory Valuation Report - Stock valuation
- **302**: Inventory Planning Report - Reorder levels
- **303**: Stock Check Sheets - Physical count sheets
- **304**: Inventory Sales Report - Sales analysis
- **305**: GRN Valuation Report - Receiving valuation
- **306**: Inventory Purchasing Report - Purchase analysis
- **307**: Inventory Movement Report - Stock movements
- **308**: Costed Inventory Movement - Movement with costs
- **309**: Item Sales Summary Report - Product sales
- **310**: Inventory Purchasing - Transaction Based - Detailed purchasing

#### General Ledger Reports (RC_GL) - 10 Reports
- **701**: Chart of Accounts - COA listing with optional balances
- **702**: List of Journal Entries - Journal register
- **704**: GL Account Transactions - Account detail
- **705**: Annual Expense Breakdown - Expense analysis by month
- **706**: Balance Sheet - Statement of financial position (CRITICAL)
- **707**: Profit and Loss Statement - Income statement (CRITICAL)
- **708**: Trial Balance - GL trial balance (CRITICAL)
- **709**: Tax Report - VAT/sales tax reporting
- **710**: Audit Trail - Transaction audit log

#### Manufacturing Reports (RC_MANUFACTURE) - 3 Reports (conditional)
- **401**: Bill of Material Listing - BOM structures
- **402**: Work Order Listing - WO status
- **409**: Print Work Orders - WO printing

#### Fixed Assets Reports (RC_FIXEDASSETS) - 1 Report (conditional)
- **451**: Fixed Assets Valuation - Asset register

#### Dimensions Reports (RC_DIMENSIONS) - 1 Report
- **501**: Dimension Summary - Project/cost center analysis

#### Banking Reports (RC_BANKING) - 2 Reports
- **601**: Bank Statement - Bank reconciliation
- **602**: Bank Statement w/ Reconcile - Interactive reconciliation

## Problems with Current Architecture

### 1. Procedural Programming
- Functions mixed with presentation logic
- No separation of concerns
- Direct database access throughout
- Global variables (`$path_to_root`, `$_POST`)

### 2. Tight PDF/Excel Coupling
```php
if ($destination)
    include_once($path_to_root . "/reporting/includes/excel_report.inc");
else
    include_once($path_to_root . "/reporting/includes/pdf_report.inc");
```
- Report logic inseparable from output format
- Cannot test business logic independently
- Cannot reuse logic for dashboards/APIs

### 3. No Testability
- Cannot unit test business logic
- Direct database queries prevent mocking
- No dependency injection
- Procedural functions hard to isolate

### 4. Duplication
- Similar queries repeated across reports
- Common calculations not shared
- No reusable components

### 5. Complex Parameter Handling
```php
$from = $_POST['PARAM_0'];
$to = $_POST['PARAM_1'];
$fromcust = $_POST['PARAM_2'];
$show_balance = $_POST['PARAM_3'];
// ... 9+ parameters
```
- Magic parameter indices
- No validation
- Type unsafe

## Target Architecture

### Module Structure
```
modules/
└── Reports/
    └── Legacy/  # New namespace for refactored FA reports
        ├── Customer/
        │   ├── CustomerBalancesReport.php
        │   ├── AgedCustomerAnalysisReport.php
        │   ├── CustomerTrialBalanceReport.php
        │   ├── CustomerDetailListingReport.php
        │   ├── SalesSummaryReport.php
        │   ├── PriceListingReport.php
        │   ├── OrderStatusListingReport.php
        │   ├── SalesmanListingReport.php
        │   ├── tests/
        │   ├── hooks_customer_balances.php
        │   └── README_*.md
        ├── Supplier/
        │   ├── SupplierBalancesReport.php
        │   ├── AgedSupplierAnalysisReport.php
        │   ├── SupplierTrialBalanceReport.php
        │   ├── PaymentReport.php
        │   ├── OutstandingGRNReport.php
        │   ├── SupplierDetailListingReport.php
        │   ├── tests/
        │   └── hooks_*.php
        ├── Inventory/
        │   ├── InventoryValuationReport.php
        │   ├── InventoryPlanningReport.php
        │   ├── StockCheckSheetsReport.php
        │   ├── InventorySalesReport.php
        │   ├── GRNValuationReport.php
        │   ├── InventoryPurchasingReport.php
        │   ├── InventoryMovementReport.php
        │   ├── CostedInventoryMovementReport.php
        │   ├── ItemSalesSummaryReport.php
        │   ├── tests/
        │   └── hooks_*.php
        ├── GL/
        │   ├── ChartOfAccountsReport.php
        │   ├── JournalEntriesReport.php
        │   ├── GLAccountTransactionsReport.php
        │   ├── AnnualExpenseBreakdownReport.php
        │   ├── BalanceSheetReport.php  # CRITICAL
        │   ├── ProfitAndLossReport.php  # CRITICAL
        │   ├── TrialBalanceReport.php   # CRITICAL
        │   ├── TaxReport.php
        │   ├── AuditTrailReport.php
        │   ├── tests/
        │   └── hooks_*.php
        ├── Banking/
        │   ├── BankStatementReport.php
        │   ├── BankReconciliationReport.php
        │   ├── tests/
        │   └── hooks_*.php
        └── Manufacturing/
            ├── BillOfMaterialReport.php
            ├── WorkOrderListingReport.php
            ├── tests/
            └── hooks_*.php
```

### Service Class Pattern
```php
<?php

declare(strict_types=1);

namespace FA\Modules\Reports\Legacy\GL;

use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

class BalanceSheetReport
{
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    public function __construct(
        DBALInterface $db,
        EventDispatcher $dispatcher,
        LoggerInterface $logger
    ) {
        $this->db = $db;
        $this->dispatcher = $dispatcher;
        $this->logger = $logger;
    }

    /**
     * Generate balance sheet
     *
     * @param string $startDate Start date (YYYY-MM-DD)
     * @param string $endDate End date (YYYY-MM-DD)
     * @param int $dimension1 First dimension filter (0 = all)
     * @param int $dimension2 Second dimension filter (0 = all)
     * @param array $tags Account tags filter
     * @return array Balance sheet data
     */
    public function generate(
        string $startDate,
        string $endDate,
        int $dimension1 = 0,
        int $dimension2 = 0,
        array $tags = []
    ): array {
        $this->logger->info('Generating balance sheet', [
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);

        return [
            'assets' => $this->getAssets($startDate, $endDate, $dimension1, $dimension2, $tags),
            'liabilities' => $this->getLiabilities($startDate, $endDate, $dimension1, $dimension2, $tags),
            'equity' => $this->getEquity($startDate, $endDate, $dimension1, $dimension2, $tags),
            'generated_at' => date('Y-m-d H:i:s')
        ];
    }

    private function getAssets(/* ... */): array { /* ... */ }
    private function getLiabilities(/* ... */): array { /* ... */ }
    private function getEquity(/* ... */): array { /* ... */ }
    
    public function exportToPDF(array $data, string $filename): string { /* ... */ }
    public function exportToExcel(array $data, string $filename): string { /* ... */ }
}
```

### Backward Compatibility Strategy

#### Phase 1: Service Layer (No Breaking Changes)
1. Create service classes in `modules/Reports/Legacy/`
2. Keep existing `reporting/rep*.php` files unchanged
3. Service classes have pure business logic
4. Comprehensive test coverage

#### Phase 2: Facade Layer (Bridge Pattern)
Update `reporting/rep*.php` to use services:
```php
<?php
// reporting/rep706.php - Balance Sheet (REFACTORED)
$page_security = 'SA_GLANALYTIC';
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
require_once($path_to_root . "/modules/Reports/Legacy/GL/BalanceSheetReport.php");

use FA\Modules\Reports\Legacy\GL\BalanceSheetReport;

// Parse $_POST parameters (legacy interface)
$startDate = $_POST['PARAM_0'];
$endDate = $_POST['PARAM_1'];
$dimension1 = (int)($_POST['PARAM_2'] ?? 0);
$dimension2 = (int)($_POST['PARAM_3'] ?? 0);
$tags = $_POST['PARAM_4'] ?? [];
$decimals = $_POST['PARAM_5'];
$graphics = $_POST['PARAM_6'];
$comments = $_POST['PARAM_7'];
$orientation = $_POST['PARAM_8'];
$destination = $_POST['PARAM_9'];

// Initialize service
$db = get_db_connection(); // Legacy adapter
$dispatcher = get_event_dispatcher();
$logger = get_logger();

$report = new BalanceSheetReport($db, $dispatcher, $logger);

// Generate data
$data = $report->generate($startDate, $endDate, $dimension1, $dimension2, $tags);

// Render using legacy PDF/Excel (maintains compatibility)
if ($destination) {
    include_once($path_to_root . "/reporting/includes/excel_report.inc");
    render_balance_sheet_excel($data, $comments, $orientation);
} else {
    include_once($path_to_root . "/reporting/includes/pdf_report.inc");
    render_balance_sheet_pdf($data, $comments, $orientation, $graphics, $decimals);
}
```

#### Phase 3: Modern Interface (Optional Enhancement)
- Dashboard widgets using service classes
- REST API endpoints
- Export to JSON/CSV
- Real-time data updates

## Refactoring Priorities

### Priority 1: Critical GL Reports (High Business Value)
**Timeline: 2 weeks**

1. **Balance Sheet (rep706.php)** - Most critical financial statement
2. **Profit & Loss (rep707.php)** - Income statement
3. **Trial Balance (rep708.php)** - GL validation
4. **GL Account Transactions (rep704.php)** - Account detail
5. **Audit Trail (rep710.php)** - Compliance/security

**Rationale:**
- Core financial reporting
- Required for financial close
- External reporting (investors, banks, auditors)
- Complex recursive logic benefits from testing

### Priority 2: Customer Reports (High Usage)
**Timeline: 2 weeks**

1. **Customer Balances (rep101.php)** - AR aging
2. **Aged Customer Analysis (rep102.php)** - Collections
3. **Sales Summary (rep114.php)** - Tax reporting
4. **Salesman Listing (rep106.php)** - Sales performance

**Rationale:**
- Daily operations
- Cash flow management
- Sales team performance

### Priority 3: Supplier Reports (Operational)
**Timeline: 1.5 weeks**

1. **Supplier Balances (rep201.php)** - AP aging
2. **Aged Supplier Analysis (rep202.php)** - Payment scheduling
3. **Outstanding GRN (rep204.php)** - Accrual management
4. **Payment Report (rep203.php)** - Cash planning

### Priority 4: Inventory Reports (Operational)
**Timeline: 2 weeks**

1. **Inventory Valuation (rep301.php)** - Balance sheet
2. **Inventory Movement (rep307.php)** - Stock analysis
3. **Costed Movement (rep308.php)** - Cost accounting
4. **Inventory Sales (rep304.php)** - Product performance

### Priority 5: Banking Reports (Lower Complexity)
**Timeline: 1 week**

1. **Bank Statement (rep601.php)**
2. **Bank Reconciliation (rep602.php)**

### Priority 6: Specialized Reports (Conditional Features)
**Timeline: 1 week**

1. **Manufacturing reports** (if use_manufacturing enabled)
2. **Fixed Assets** (if use_fixed_assets enabled)
3. **Dimensions** (if dimensions > 0)

## Implementation Approach per Report

### Step 1: Analyze Existing Report
```bash
# Read legacy report
# Identify:
# - Database queries
# - Business logic
# - Calculations
# - Parameter handling
# - Output format requirements
```

### Step 2: Create Service Class
```php
// modules/Reports/Legacy/GL/BalanceSheetReport.php
// - Constructor with dependencies
// - generate() main method
// - Private helper methods for queries
// - Export methods (PDF/Excel/JSON)
```

### Step 3: Create Comprehensive Tests
```php
// tests/Reports/Legacy/GL/BalanceSheetReportTest.php
// - Mock database responses
// - Test all calculations
// - Test edge cases
// - Test filtering (dimensions, tags, dates)
// - Achieve 90%+ code coverage
```

### Step 4: Verify TDD Red Phase
```bash
phpunit tests/Reports/Legacy/GL/BalanceSheetReportTest.php
# All tests fail - class doesn't exist yet
```

### Step 5: Implement Service (Green Phase)
```php
// Implement all methods
// Make all tests pass
```

### Step 6: Integration Hooks
```php
// modules/Reports/Legacy/GL/hooks_balance_sheet.php
// - Register report in BoxReports
// - Dashboard widget (optional)
// - Menu integration
```

### Step 7: Documentation
```markdown
// modules/Reports/Legacy/GL/README_BalanceSheet.md
// - Usage examples
// - Business logic explanation
// - Migration guide from legacy
// - API documentation
```

### Step 8: Update Legacy File (Optional - Phase 2)
```php
// reporting/rep706.php
// - Add service instantiation
// - Call service->generate()
// - Keep existing PDF/Excel rendering
// - 100% backward compatible
```

## Testing Strategy

### Unit Tests (Mandatory)
- Test each report service class
- Mock all database calls
- Test calculations and business logic
- Test edge cases (no data, single record, large datasets)
- Minimum 85% code coverage

### Integration Tests (Recommended)
- Test against actual database
- Verify SQL query correctness
- Test with real company data
- Compare output with legacy reports

### Regression Tests (Critical)
- Generate reports with both legacy and new system
- Compare numerical outputs
- Verify totals match
- Document any intentional differences

## Common Patterns to Extract

### 1. Date Range Queries
```php
trait DateRangeQueryTrait
{
    protected function buildDateRangeCondition(
        string $dateColumn,
        string $startDate,
        string $endDate
    ): string {
        $start = DateService::date2sqlStatic($startDate);
        $end = DateService::date2sqlStatic($endDate);
        return "$dateColumn >= " . db_escape($start) . 
               " AND $dateColumn <= " . db_escape($end);
    }
}
```

### 2. Dimension Filtering
```php
trait DimensionFilterTrait
{
    protected function buildDimensionCondition(
        int $dimension1,
        int $dimension2
    ): string {
        $conditions = [];
        if ($dimension1 > 0) {
            $conditions[] = "dimension_id = " . (int)$dimension1;
        }
        if ($dimension2 > 0) {
            $conditions[] = "dimension2_id = " . (int)$dimension2;
        }
        return !empty($conditions) ? implode(' AND ', $conditions) : '1=1';
    }
}
```

### 3. Account Type Recursion
```php
class AccountTypeIterator
{
    public function getAccountsRecursive(int $typeId, array $filters = []): array
    {
        // Recursive logic for hierarchical COA
        // Used by Balance Sheet, P&L, etc.
    }
}
```

### 4. Currency Conversion
```php
trait CurrencyConversionTrait
{
    protected function convertAmount(
        float $amount,
        string $fromCurrency,
        string $toCurrency,
        string $date
    ): float {
        // Exchange rate conversion logic
    }
}
```

### 5. Zero Suppression
```php
trait ZeroSuppressionTrait
{
    protected function filterZeros(array $data, bool $suppressZeros): array
    {
        if (!$suppressZeros) {
            return $data;
        }
        return array_filter($data, fn($row) => $row['amount'] != 0);
    }
}
```

## Database Abstraction Considerations

### Current Direct Queries
```php
$sql = "SELECT * FROM ".TB_PREF."debtor_trans WHERE ...";
$result = db_query($sql, "Error message");
```

### Target: DBALInterface
```php
$sql = "SELECT * FROM {$this->db->prefix()}debtor_trans WHERE ...";
$result = $this->db->fetchAll($sql);
```

### Query Repository Pattern (Future Enhancement)
```php
class CustomerBalanceRepository
{
    public function getOpenBalance(int $debtorNo, string $toDate): array
    {
        // Encapsulate complex query
    }
    
    public function getTransactions(
        int $debtorNo,
        string $from,
        string $to
    ): array {
        // Encapsulate transaction query
    }
}
```

## Migration Path for Users

### No User Action Required
- Reports continue working exactly as before
- Same menu locations
- Same parameter screens
- Same output format (PDF/Excel)

### Optional Enhancements Available
- Dashboard widgets for key reports
- API access to report data
- New export formats (JSON, CSV)
- Real-time data (no PDF generation)

## Success Metrics

### Code Quality
- [ ] 90%+ test coverage for all refactored reports
- [ ] 0 direct `$_POST` access in service classes
- [ ] 0 direct `db_query()` calls in service classes
- [ ] PSR-12 coding standards compliance
- [ ] PHPStan level 6+ passes

### Backward Compatibility
- [ ] All existing reports produce identical output
- [ ] No changes to report parameters
- [ ] No changes to menu structure
- [ ] No database schema changes required

### Performance
- [ ] Report generation time unchanged or improved
- [ ] Memory usage unchanged or reduced
- [ ] Database query count unchanged or reduced

### Documentation
- [ ] README for each refactored report
- [ ] Migration guide for developers
- [ ] API documentation
- [ ] Usage examples

## Timeline Estimate

### Phase 1: GL Reports (Critical Path)
- **Week 1-2**: Balance Sheet, P&L, Trial Balance (3 reports)
- **Week 3**: GL Transactions, Audit Trail (2 reports)
- **Deliverable**: 5 GL reports fully refactored with tests

### Phase 2: Customer Reports
- **Week 4-5**: Customer Balances, Aged Analysis, Sales Summary, Salesman (4 reports)
- **Week 6**: Customer detail, Price listing (2 reports)
- **Deliverable**: 6 customer reports refactored

### Phase 3: Supplier Reports
- **Week 7-8**: Supplier Balances, Aged Analysis, Outstanding GRN, Payment Report (4 reports)
- **Deliverable**: 4 supplier reports refactored

### Phase 4: Inventory Reports
- **Week 9-10**: Valuation, Movement, Sales, Planning (4 reports)
- **Deliverable**: 4 inventory reports refactored

### Phase 5: Banking & Specialized
- **Week 11**: Banking reports (2 reports)
- **Week 12**: Manufacturing, Fixed Assets (conditional)
- **Deliverable**: Remaining reports refactored

### Total: 12 weeks for complete refactoring

## Risk Mitigation

### Risk: Breaking Existing Functionality
**Mitigation:**
- Comprehensive unit tests before refactoring
- Integration tests comparing old vs. new output
- Phased rollout (service layer first, integration second)
- Feature flag to toggle between old and new

### Risk: Performance Degradation
**Mitigation:**
- Benchmark existing report performance
- Profile new implementation
- Optimize queries if needed
- Cache common calculations

### Risk: Complex Recursive Logic
**Mitigation:**
- Start with simpler reports (Customer Balances)
- Build up to complex reports (Balance Sheet, P&L)
- Extract common recursive patterns into traits
- Extensive testing of edge cases

### Risk: Incomplete Requirements Understanding
**Mitigation:**
- Compare output with legacy reports
- Review with accounting/business users
- Document all business rules
- Test with real company data

## Next Steps

1. **Review this plan** - Validate approach and priorities
2. **Select first report** - Recommend starting with rep702 (Journal Entries) as warm-up
3. **Create service template** - Establish pattern for others to follow
4. **Implement first report** - Full TDD cycle with all documentation
5. **Review and iterate** - Refine approach based on learnings
6. **Scale to remaining reports** - Apply established pattern

## Questions for Decision

1. **Scope**: Refactor all 50+ reports or prioritize subset?
2. **Timeline**: Aggressive (12 weeks) or conservative (6 months)?
3. **Testing**: Unit tests only or include integration tests?
4. **Documentation**: Minimal or comprehensive READMEs?
5. **Dashboard widgets**: Create for all reports or just key ones?
6. **API endpoints**: Provide REST API access to reports?
7. **Export formats**: Keep PDF/Excel only or add JSON/CSV?

## Conclusion

This refactoring will modernize FrontAccounting's reporting system while maintaining 100% backward compatibility. The service-oriented architecture enables:

- **Testability**: Comprehensive unit tests
- **Reusability**: Service classes usable in dashboards, APIs, integrations
- **Maintainability**: Clear separation of concerns
- **Extensibility**: Easy to add new reports following established patterns
- **Performance**: Opportunity to optimize queries and caching

The phased approach minimizes risk while delivering value incrementally, starting with the most critical financial reports.
