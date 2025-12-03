<?php
/**
 * Annual Expense Breakdown Report Test Suite
 * 
 * Tests comprehensive annual expense reporting functionality with period comparison,
 * category breakdown, and budget variance analysis following TDD methodology.
 * 
 * @package    KSF\Reports
 * @subpackage Financial
 * @author     KSF Development Team
 * @copyright  2025 KSFraser
 * @license    MIT
 * @version    1.0.0
 * @link       https://github.com/ksfraser/ksf_Reports
 */

declare(strict_types=1);

namespace KSF\Tests\Reports\Financial;

use PHPUnit\Framework\TestCase;
use KSF\Reports\Financial\AnnualExpenseBreakdownReport;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

// Manual requires for submodule classes until autoloader fully configured
require_once __DIR__ . '/../../../includes/Database/DBALInterface.php';
require_once __DIR__ . '/../../../includes/Events/EventDispatcher.php';
require_once __DIR__ . '/../../../modules/Reports/Events.php';
require_once __DIR__ . '/../../../modules/Reports/Financial/AnnualExpenseBreakdownReport.php';

/**
 * Test suite for Annual Expense Breakdown Report
 * 
 * Validates expense categorization, period comparison, budget analysis,
 * trend calculations, and export functionality.
 */
class AnnualExpenseBreakdownTest extends TestCase
{
    private DBALInterface $dbal;
    private EventDispatcher $eventDispatcher;
    private LoggerInterface $logger;
    private AnnualExpenseBreakdownReport $report;

    /**
     * Set up test fixtures
     */
    protected function setUp(): void
    {
        $this->dbal = $this->createMock(DBALInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->report = new AnnualExpenseBreakdownReport(
            $this->dbal,
            $this->eventDispatcher,
            $this->logger
        );
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_generates_annual_expense_breakdown_for_single_year(): void
    {
        // Mock expense data for fiscal year
        $mockExpenses = [
            [
                'category' => 'Salaries & Wages',
                'account_code' => '5000',
                'account_name' => 'Salaries',
                'amount' => 250000.00,
                'budget' => 240000.00,
                'variance' => 10000.00,
                'variance_percent' => 4.17,
                'transactions' => 12
            ],
            [
                'category' => 'Salaries & Wages',
                'account_code' => '5010',
                'account_name' => 'Payroll Taxes',
                'amount' => 35000.00,
                'budget' => 33600.00,
                'variance' => 1400.00,
                'variance_percent' => 4.17,
                'transactions' => 12
            ],
            [
                'category' => 'Operating Expenses',
                'account_code' => '5100',
                'account_name' => 'Rent',
                'amount' => 60000.00,
                'budget' => 60000.00,
                'variance' => 0.00,
                'variance_percent' => 0.00,
                'transactions' => 12
            ],
            [
                'category' => 'Operating Expenses',
                'account_code' => '5110',
                'account_name' => 'Utilities',
                'amount' => 18500.00,
                'budget' => 20000.00,
                'variance' => -1500.00,
                'variance_percent' => -7.50,
                'transactions' => 12
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockExpenses);

        $params = [
            'fiscal_year' => 2024,
            'include_budget' => true,
            'group_by_category' => true
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('totals', $result);
        $this->assertArrayHasKey('metadata', $result);
        
        // Verify category grouping
        $this->assertCount(2, $result['categories']);
        $this->assertArrayHasKey('Salaries & Wages', $result['categories']);
        $this->assertArrayHasKey('Operating Expenses', $result['categories']);
        
        // Verify totals
        $this->assertEquals(363500.00, $result['totals']['actual']);
        $this->assertEquals(353600.00, $result['totals']['budget']);
        $this->assertEquals(9900.00, $result['totals']['variance']);
        $this->assertEquals(2.80, round($result['totals']['variance_percent'], 2));
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_compares_expenses_across_multiple_years(): void
    {
        $mockComparison = [
            [
                'category' => 'Salaries & Wages',
                'year_2023' => 270000.00,
                'year_2024' => 285000.00,
                'change' => 15000.00,
                'change_percent' => 5.56
            ],
            [
                'category' => 'Operating Expenses',
                'year_2023' => 75000.00,
                'year_2024' => 78500.00,
                'change' => 3500.00,
                'change_percent' => 4.67
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockComparison);

        $params = [
            'compare_years' => [2023, 2024],
            'group_by_category' => true
        ];

        $result = $this->report->generateComparison($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('comparison', $result);
        $this->assertCount(2, $result['comparison']);
        
        // Verify year-over-year changes
        foreach ($result['comparison'] as $item) {
            $this->assertArrayHasKey('change', $item);
            $this->assertArrayHasKey('change_percent', $item);
        }
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_calculates_monthly_expense_trends(): void
    {
        $mockTrends = [];
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        
        foreach ($months as $index => $month) {
            $mockTrends[] = [
                'month' => $month,
                'period' => $index + 1,
                'expenses' => 25000.00 + ($index * 500), // Gradually increasing
                'budget' => 27000.00,
                'variance' => 2000.00 - ($index * 500)
            ];
        }

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTrends);

        $params = [
            'fiscal_year' => 2024,
            'show_trends' => true
        ];

        $result = $this->report->generateTrends($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('trends', $result);
        $this->assertCount(12, $result['trends']);
        
        // Verify trend calculation
        $firstMonth = $result['trends'][0]['expenses'];
        $lastMonth = $result['trends'][11]['expenses'];
        $this->assertGreaterThan($firstMonth, $lastMonth);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_identifies_top_expense_accounts(): void
    {
        $mockTopExpenses = [
            [
                'account_code' => '5000',
                'account_name' => 'Salaries',
                'amount' => 250000.00,
                'percent_of_total' => 45.23
            ],
            [
                'account_code' => '5100',
                'account_name' => 'Rent',
                'amount' => 60000.00,
                'percent_of_total' => 10.85
            ],
            [
                'account_code' => '5010',
                'account_name' => 'Payroll Taxes',
                'amount' => 35000.00,
                'percent_of_total' => 6.33
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTopExpenses);

        $params = [
            'fiscal_year' => 2024,
            'top_accounts' => 10
        ];

        $result = $this->report->generateTopExpenses($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('top_expenses', $result);
        $this->assertLessThanOrEqual(10, count($result['top_expenses']));
        
        // Verify sorting by amount descending
        $amounts = array_column($result['top_expenses'], 'amount');
        $this->assertEquals($amounts, array_values(array_reverse($amounts, true)));
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_filters_by_expense_category(): void
    {
        $mockCategoryExpenses = [
            [
                'account_code' => '5000',
                'account_name' => 'Salaries',
                'amount' => 250000.00
            ],
            [
                'account_code' => '5010',
                'account_name' => 'Payroll Taxes',
                'amount' => 35000.00
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockCategoryExpenses);

        $params = [
            'fiscal_year' => 2024,
            'category_filter' => 'Salaries & Wages'
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('category_filter', $result['metadata']);
        $this->assertEquals('Salaries & Wages', $result['metadata']['category_filter']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_calculates_budget_variance_analysis(): void
    {
        $mockVariances = [
            [
                'category' => 'Salaries & Wages',
                'budget' => 280000.00,
                'actual' => 285000.00,
                'variance' => 5000.00,
                'variance_percent' => 1.79,
                'status' => 'over_budget'
            ],
            [
                'category' => 'Operating Expenses',
                'budget' => 80000.00,
                'actual' => 78500.00,
                'variance' => -1500.00,
                'variance_percent' => -1.88,
                'status' => 'under_budget'
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockVariances);

        $params = [
            'fiscal_year' => 2024,
            'variance_threshold' => 5.0 // Alert if variance > 5%
        ];

        $result = $this->report->generateVarianceAnalysis($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('variances', $result);
        
        foreach ($result['variances'] as $variance) {
            $this->assertArrayHasKey('status', $variance);
            $this->assertContains($variance['status'], ['over_budget', 'under_budget', 'on_budget']);
        }
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_exports_to_pdf_format(): void
    {
        $mockExpenses = [
            ['category' => 'Salaries', 'amount' => 250000.00],
            ['category' => 'Rent', 'amount' => 60000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockExpenses);

        $params = [
            'fiscal_year' => 2024,
            'format' => 'pdf'
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('pdf', $params['format']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_exports_to_excel_format(): void
    {
        $mockExpenses = [
            ['category' => 'Salaries', 'amount' => 250000.00],
            ['category' => 'Rent', 'amount' => 60000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockExpenses);

        $params = [
            'fiscal_year' => 2024,
            'format' => 'excel'
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('excel', $params['format']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('fiscal_year parameter is required');

        $params = []; // Missing fiscal_year

        $this->report->generate($params);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_handles_no_expense_data_gracefully(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $params = [
            'fiscal_year' => 2024
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertEmpty($result['categories']);
        $this->assertEquals(0.00, $result['totals']['actual']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_includes_metadata_in_report(): void
    {
        $mockExpenses = [
            ['category' => 'Salaries', 'amount' => 250000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockExpenses);

        $params = [
            'fiscal_year' => 2024
        ];

        $result = $this->report->generate($params);

        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('generated_at', $result['metadata']);
        $this->assertArrayHasKey('fiscal_year', $result['metadata']);
        $this->assertArrayHasKey('report_type', $result['metadata']);
        $this->assertEquals('annual_expense_breakdown', $result['metadata']['report_type']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     */
    public function it_groups_expenses_by_quarter(): void
    {
        $mockQuarterly = [
            ['quarter' => 'Q1', 'expenses' => 85000.00],
            ['quarter' => 'Q2', 'expenses' => 90000.00],
            ['quarter' => 'Q3', 'expenses' => 88000.00],
            ['quarter' => 'Q4', 'expenses' => 92000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockQuarterly);

        $params = [
            'fiscal_year' => 2024,
            'group_by_quarter' => true
        ];

        $result = $this->report->generateQuarterly($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('quarters', $result);
        $this->assertCount(4, $result['quarters']);
    }
}
