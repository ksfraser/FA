<?php
/**
 * Aged Debtors Report Test Suite
 * 
 * Tests comprehensive accounts receivable aging functionality with customer analysis,
 * aging bucket calculations, overdue tracking, and credit limit monitoring.
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
use KSF\Reports\Financial\AgedDebtorsReport;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

// Manual requires for submodule classes
require_once __DIR__ . '/../../../includes/Database/DBALInterface.php';
require_once __DIR__ . '/../../../includes/Events/EventDispatcher.php';
require_once __DIR__ . '/../../../modules/Reports/Events.php';
require_once __DIR__ . '/../../../modules/Reports/Financial/AgedDebtorsReport.php';

/**
 * Test suite for Aged Debtors Report
 * 
 * Validates aging calculations, customer grouping, overdue analysis,
 * credit limit monitoring, and collection priority determination.
 */
class AgedDebtorsReportTest extends TestCase
{
    private DBALInterface $dbal;
    private EventDispatcher $eventDispatcher;
    private LoggerInterface $logger;
    private AgedDebtorsReport $report;

    protected function setUp(): void
    {
        $this->dbal = $this->createMock(DBALInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        
        $this->report = new AgedDebtorsReport(
            $this->dbal,
            $this->eventDispatcher,
            $this->logger
        );
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_generates_aged_debtors_report_with_standard_buckets(): void
    {
        $mockDebtors = [
            [
                'customer_id' => 1,
                'customer_name' => 'ABC Corporation',
                'current' => 5000.00,
                'days_30' => 3000.00,
                'days_60' => 2000.00,
                'days_90' => 1000.00,
                'days_over_90' => 500.00,
                'total_due' => 11500.00,
                'credit_limit' => 15000.00,
                'currency' => 'USD'
            ],
            [
                'customer_id' => 2,
                'customer_name' => 'XYZ Industries',
                'current' => 8000.00,
                'days_30' => 0.00,
                'days_60' => 0.00,
                'days_90' => 0.00,
                'days_over_90' => 0.00,
                'total_due' => 8000.00,
                'credit_limit' => 10000.00,
                'currency' => 'USD'
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'aging_buckets' => [0, 30, 60, 90]
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('customers', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('metadata', $result);
        
        $this->assertCount(2, $result['customers']);
        $this->assertEquals(19500.00, $result['summary']['total_outstanding']);
        $this->assertEquals(11500.00, $result['customers'][0]['total_due']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_calculates_aging_buckets_correctly(): void
    {
        $mockTransactions = [
            [
                'customer_id' => 1,
                'customer_name' => 'Test Customer',
                'trans_no' => 'INV-001',
                'trans_date' => '2024-11-15',
                'due_date' => '2024-12-15',
                'amount' => 1000.00,
                'paid' => 0.00,
                'balance' => 1000.00,
                'days_overdue' => 16
            ],
            [
                'customer_id' => 1,
                'customer_name' => 'Test Customer',
                'trans_no' => 'INV-002',
                'trans_date' => '2024-10-01',
                'due_date' => '2024-11-01',
                'amount' => 2000.00,
                'paid' => 500.00,
                'balance' => 1500.00,
                'days_overdue' => 60
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockTransactions);

        $params = [
            'as_of_date' => '2024-12-31',
            'show_details' => true
        ];

        $result = $this->report->generateDetailed($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('transactions', $result);
        
        $customer = $result['customers'][0];
        $this->assertEquals(0.00, $customer['current']);
        $this->assertEquals(1000.00, $customer['days_30']);
        $this->assertEquals(1500.00, $customer['days_60']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_identifies_customers_over_credit_limit(): void
    {
        $mockDebtors = [
            [
                'customer_id' => 1,
                'customer_name' => 'Over Limit Corp',
                'total_due' => 12000.00,
                'credit_limit' => 10000.00,
                'currency' => 'USD'
            ],
            [
                'customer_id' => 2,
                'customer_name' => 'Within Limit Inc',
                'total_due' => 5000.00,
                'credit_limit' => 10000.00,
                'currency' => 'USD'
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'show_credit_alerts' => true
        ];

        $result = $this->report->generateCreditAlerts($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('over_limit', $result);
        $this->assertCount(1, $result['over_limit']);
        $this->assertEquals('Over Limit Corp', $result['over_limit'][0]['customer_name']);
        $this->assertEquals(2000.00, $result['over_limit'][0]['over_limit_amount']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_calculates_collection_priority_scores(): void
    {
        $mockDebtors = [
            [
                'customer_id' => 1,
                'customer_name' => 'High Priority',
                'days_over_90' => 5000.00,
                'total_due' => 10000.00,
                'credit_limit' => 8000.00
            ],
            [
                'customer_id' => 2,
                'customer_name' => 'Low Priority',
                'days_over_90' => 0.00,
                'total_due' => 1000.00,
                'credit_limit' => 10000.00
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31'
        ];

        $result = $this->report->generatePriorityList($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('priority_customers', $result);
        
        // High priority should be first
        $this->assertEquals('High Priority', $result['priority_customers'][0]['customer_name']);
        $this->assertGreaterThan(
            $result['priority_customers'][1]['priority_score'],
            $result['priority_customers'][0]['priority_score']
        );
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_groups_debtors_by_currency(): void
    {
        $mockDebtors = [
            ['customer_id' => 1, 'customer_name' => 'US Customer', 'total_due' => 5000.00, 'currency' => 'USD'],
            ['customer_id' => 2, 'customer_name' => 'UK Customer', 'total_due' => 3000.00, 'currency' => 'GBP'],
            ['customer_id' => 3, 'customer_name' => 'EU Customer', 'total_due' => 4000.00, 'currency' => 'EUR']
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'group_by_currency' => true
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('by_currency', $result);
        $this->assertCount(3, $result['by_currency']);
        $this->assertArrayHasKey('USD', $result['by_currency']);
        $this->assertArrayHasKey('GBP', $result['by_currency']);
        $this->assertArrayHasKey('EUR', $result['by_currency']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_filters_by_customer_type(): void
    {
        $mockDebtors = [
            ['customer_id' => 1, 'customer_name' => 'Retail Customer', 'customer_type' => 'retail', 'total_due' => 1000.00],
            ['customer_id' => 2, 'customer_name' => 'Wholesale Customer', 'customer_type' => 'wholesale', 'total_due' => 5000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'customer_type_filter' => 'wholesale'
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('metadata', $result);
        $this->assertEquals('wholesale', $result['metadata']['customer_type_filter']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_calculates_days_sales_outstanding(): void
    {
        $mockData = [
            'total_receivables' => 100000.00,
            'total_sales' => 365000.00,
            'period_days' => 365
        ];

        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn($mockData);

        $params = [
            'as_of_date' => '2024-12-31',
            'calculate_dso' => true
        ];

        $result = $this->report->generateMetrics($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertArrayHasKey('dso', $result['metrics']);
        
        // DSO = (Receivables / Sales) * Days = (100000 / 365000) * 365 = 100 days
        $this->assertEquals(100.0, round($result['metrics']['dso'], 1));
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_generates_aging_summary_by_percentage(): void
    {
        $mockDebtors = [
            [
                'current' => 10000.00,
                'days_30' => 5000.00,
                'days_60' => 3000.00,
                'days_90' => 1500.00,
                'days_over_90' => 500.00,
                'total_due' => 20000.00
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'show_percentages' => true
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('percentages', $result['summary']);
        
        $percentages = $result['summary']['percentages'];
        $this->assertEquals(50.0, $percentages['current']);
        $this->assertEquals(25.0, $percentages['days_30']);
        $this->assertEquals(15.0, $percentages['days_60']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_validates_required_parameters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('as_of_date parameter is required');

        $this->report->generate([]);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_handles_no_outstanding_debtors(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn([]);

        $params = ['as_of_date' => '2024-12-31'];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertEmpty($result['customers']);
        $this->assertEquals(0.00, $result['summary']['total_outstanding']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_includes_contact_information_when_requested(): void
    {
        $mockDebtors = [
            [
                'customer_id' => 1,
                'customer_name' => 'Test Customer',
                'contact_name' => 'John Doe',
                'contact_phone' => '555-1234',
                'contact_email' => 'john@test.com',
                'total_due' => 5000.00
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'include_contacts' => true
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $customer = $result['customers'][0];
        $this->assertArrayHasKey('contact_name', $customer);
        $this->assertEquals('John Doe', $customer['contact_name']);
        $this->assertEquals('555-1234', $customer['contact_phone']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_exports_to_pdf_format(): void
    {
        $mockDebtors = [
            ['customer_id' => 1, 'customer_name' => 'Test', 'total_due' => 1000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'format' => 'pdf'
        ];

        $result = $this->report->generate($params);

        $this->assertIsArray($result);
        $this->assertEquals('pdf', $params['format']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_generates_collection_letters_data(): void
    {
        $mockDebtors = [
            [
                'customer_id' => 1,
                'customer_name' => 'Overdue Customer',
                'days_over_90' => 5000.00,
                'total_due' => 8000.00,
                'last_payment_date' => '2024-09-15'
            ]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = [
            'as_of_date' => '2024-12-31',
            'overdue_only' => true,
            'min_days_overdue' => 90
        ];

        $result = $this->report->generateCollectionLetters($params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('collection_required', $result);
        $this->assertCount(1, $result['collection_required']);
    }

    /**
     * @test
     * @group reports
     * @group financial
     * @group ar
     */
    public function it_includes_metadata_in_report(): void
    {
        $mockDebtors = [
            ['customer_id' => 1, 'customer_name' => 'Test', 'total_due' => 1000.00]
        ];

        $this->dbal->expects($this->once())
            ->method('fetchAll')
            ->willReturn($mockDebtors);

        $params = ['as_of_date' => '2024-12-31'];

        $result = $this->report->generate($params);

        $this->assertArrayHasKey('metadata', $result);
        $this->assertArrayHasKey('generated_at', $result['metadata']);
        $this->assertArrayHasKey('as_of_date', $result['metadata']);
        $this->assertArrayHasKey('report_type', $result['metadata']);
        $this->assertEquals('aged_debtors', $result['metadata']['report_type']);
    }
}
