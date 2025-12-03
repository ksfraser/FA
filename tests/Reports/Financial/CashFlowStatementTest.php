<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Financial;

use FA\Modules\Reports\Financial\CashFlowStatementReport;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test suite for Cash Flow Statement Report (Indirect Method)
 * 
 * Tests operating, investing, and financing activities calculations,
 * net income adjustments, and comprehensive cash flow analysis.
 * 
 * @package FA\Tests\Reports\Financial
 * @author FrontAccounting Development Team
 */
class CashFlowStatementTest extends TestCase
{
    private CashFlowStatementReport $report;
    private DBALInterface|MockObject $dbal;
    private EventDispatcher|MockObject $eventDispatcher;
    private LoggerInterface|MockObject $logger;

    protected function setUp(): void
    {
        $this->dbal = $this->createMock(DBALInterface::class);
        $this->eventDispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new CashFlowStatementReport(
            $this->dbal,
            $this->eventDispatcher,
            $this->logger
        );
    }

    public function testGenerateCashFlowStatementBasic(): void
    {
        // Mock net income
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->with($this->callback(function ($sql) {
                return stripos($sql, 'net income') !== false;
            }))
            ->willReturn(100000.00);

        // Mock operating activities adjustments
        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Depreciation and amortization
                [['total' => 15000.00]],
                // Changes in working capital
                [
                    ['account' => 'Accounts Receivable', 'change' => -5000.00],
                    ['account' => 'Inventory', 'change' => -10000.00],
                    ['account' => 'Accounts Payable', 'change' => 8000.00],
                ],
                // Investing activities
                [
                    ['activity' => 'Purchase of Equipment', 'amount' => -25000.00],
                    ['activity' => 'Sale of Investment', 'amount' => 5000.00],
                ],
                // Financing activities
                [
                    ['activity' => 'Loan Proceeds', 'amount' => 50000.00],
                    ['activity' => 'Dividend Payments', 'amount' => -10000.00],
                ]
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('operating_activities', $result);
        $this->assertArrayHasKey('investing_activities', $result);
        $this->assertArrayHasKey('financing_activities', $result);
        $this->assertArrayHasKey('net_cash_change', $result);
        
        // Net income + depreciation (15k) - AR increase (5k) - Inv increase (10k) + AP increase (8k)
        $this->assertEquals(108000.00, $result['operating_activities']['net_cash_from_operating']);
        
        // Equipment purchase (-25k) + Investment sale (5k)
        $this->assertEquals(-20000.00, $result['investing_activities']['net_cash_from_investing']);
        
        // Loan (50k) - Dividends (10k)
        $this->assertEquals(40000.00, $result['financing_activities']['net_cash_from_financing']);
        
        // Operating (108k) + Investing (-20k) + Financing (40k)
        $this->assertEquals(128000.00, $result['net_cash_change']);
    }

    public function testGenerateWithDetailedOperatingActivities(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(150000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 20000.00]], // Depreciation
                [
                    ['account' => 'Accounts Receivable', 'change' => 10000.00],
                    ['account' => 'Inventory', 'change' => -15000.00],
                    ['account' => 'Prepaid Expenses', 'change' => -2000.00],
                    ['account' => 'Accounts Payable', 'change' => 12000.00],
                    ['account' => 'Accrued Liabilities', 'change' => 3000.00],
                ],
                [], // No investing
                []  // No financing
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31', true);

        $this->assertArrayHasKey('operating_activities', $result);
        $this->assertArrayHasKey('net_income', $result['operating_activities']);
        $this->assertArrayHasKey('adjustments', $result['operating_activities']);
        $this->assertArrayHasKey('working_capital_changes', $result['operating_activities']);
        
        $this->assertEquals(150000.00, $result['operating_activities']['net_income']);
        $this->assertEquals(20000.00, $result['operating_activities']['adjustments']['depreciation_amortization']);
        
        // 150k + 20k (depr) + 10k (AR decrease) - 15k (Inv increase) - 2k (prepaid) + 12k (AP) + 3k (accrued)
        $this->assertEquals(178000.00, $result['operating_activities']['net_cash_from_operating']);
    }

    public function testGenerateWithInvestingActivities(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(80000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 10000.00]],
                [],
                [
                    ['activity' => 'Purchase of Property', 'amount' => -150000.00],
                    ['activity' => 'Purchase of Equipment', 'amount' => -50000.00],
                    ['activity' => 'Sale of Old Equipment', 'amount' => 15000.00],
                    ['activity' => 'Purchase of Marketable Securities', 'amount' => -25000.00],
                    ['activity' => 'Sale of Investment', 'amount' => 30000.00],
                ],
                []
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31', true);

        $this->assertArrayHasKey('investing_activities', $result);
        $this->assertIsArray($result['investing_activities']['details']);
        $this->assertCount(5, $result['investing_activities']['details']);
        
        // -150k - 50k + 15k - 25k + 30k
        $this->assertEquals(-180000.00, $result['investing_activities']['net_cash_from_investing']);
    }

    public function testGenerateWithFinancingActivities(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(120000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 18000.00]],
                [],
                [],
                [
                    ['activity' => 'Proceeds from Bank Loan', 'amount' => 100000.00],
                    ['activity' => 'Repayment of Loan Principal', 'amount' => -25000.00],
                    ['activity' => 'Issuance of Common Stock', 'amount' => 50000.00],
                    ['activity' => 'Dividend Payments', 'amount' => -20000.00],
                    ['activity' => 'Share Buyback', 'amount' => -15000.00],
                ]
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31', true);

        $this->assertArrayHasKey('financing_activities', $result);
        $this->assertIsArray($result['financing_activities']['details']);
        $this->assertCount(5, $result['financing_activities']['details']);
        
        // 100k - 25k + 50k - 20k - 15k
        $this->assertEquals(90000.00, $result['financing_activities']['net_cash_from_financing']);
    }

    public function testGenerateWithZeroCashFlow(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(50000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 10000.00]],
                [['account' => 'Accounts Receivable', 'change' => -60000.00]],
                [],
                []
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertEquals(0.00, $result['net_cash_change']);
    }

    public function testGenerateWithNegativeCashFlow(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(-20000.00); // Net loss

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 8000.00]],
                [
                    ['account' => 'Accounts Receivable', 'change' => -15000.00],
                    ['account' => 'Inventory', 'change' => -10000.00],
                ],
                [['activity' => 'Purchase of Equipment', 'amount' => -30000.00]],
                []
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertLessThan(0, $result['net_cash_change']);
        // -20k + 8k - 15k - 10k = -37k (operating) - 30k (investing) = -67k
        $this->assertEquals(-67000.00, $result['net_cash_change']);
    }

    public function testGenerateMetrics(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(100000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 15000.00]],
                [],
                [['activity' => 'Purchase of Equipment', 'amount' => -25000.00]],
                [['activity' => 'Dividend Payments', 'amount' => -10000.00]]
            );

        $result = $this->report->generateMetrics('2024-01-01', '2024-12-31', 50000.00);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('operating_cash_flow_ratio', $result);
        $this->assertArrayHasKey('free_cash_flow', $result);
        $this->assertArrayHasKey('cash_flow_coverage_ratio', $result);
        
        // Operating cash flow = 115k
        $this->assertEquals(115000.00, $result['operating_cash_flow']);
        
        // Free cash flow = Operating (115k) - CapEx (25k)
        $this->assertEquals(90000.00, $result['free_cash_flow']);
        
        // Operating cash flow ratio = Operating / Current Liabilities
        $this->assertEquals(2.30, $result['operating_cash_flow_ratio']); // 115k / 50k
    }

    public function testGenerateComparison(): void
    {
        // Mock current period
        $this->dbal->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(100000.00, 80000.00);

        $this->dbal->expects($this->exactly(8))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Current period
                [['total' => 15000.00]],
                [],
                [],
                [],
                // Previous period
                [['total' => 12000.00]],
                [],
                [],
                []
            );

        $result = $this->report->generateComparison('2024-01-01', '2024-12-31', '2023-01-01', '2023-12-31');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('current_period', $result);
        $this->assertArrayHasKey('previous_period', $result);
        $this->assertArrayHasKey('variance', $result);
        $this->assertArrayHasKey('variance_percentage', $result);
        
        $this->assertEquals(115000.00, $result['current_period']['operating_activities']['net_cash_from_operating']);
        $this->assertEquals(92000.00, $result['previous_period']['operating_activities']['net_cash_from_operating']);
        $this->assertEquals(23000.00, $result['variance']['operating_activities']);
        $this->assertEquals(25.00, $result['variance_percentage']['operating_activities']); // (23k/92k)*100
    }

    public function testGenerateQuarterly(): void
    {
        $this->dbal->expects($this->exactly(4))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(25000.00, 30000.00, 28000.00, 32000.00);

        $this->dbal->expects($this->exactly(16))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Q1
                [['total' => 4000.00]], [], [], [],
                // Q2
                [['total' => 5000.00]], [], [], [],
                // Q3
                [['total' => 4500.00]], [], [], [],
                // Q4
                [['total' => 5500.00]], [], [], []
            );

        $result = $this->report->generateQuarterly(2024);

        $this->assertIsArray($result);
        $this->assertCount(4, $result);
        $this->assertArrayHasKey('Q1', $result);
        $this->assertArrayHasKey('Q4', $result);
        
        $this->assertEquals(29000.00, $result['Q1']['operating_activities']['net_cash_from_operating']);
        $this->assertEquals(35000.00, $result['Q2']['operating_activities']['net_cash_from_operating']);
        $this->assertEquals(32500.00, $result['Q3']['operating_activities']['net_cash_from_operating']);
        $this->assertEquals(37500.00, $result['Q4']['operating_activities']['net_cash_from_operating']);
    }

    public function testGenerateWithBeginningAndEndingCash(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(100000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 15000.00]],
                [],
                [],
                []
            );

        $result = $this->report->generate('2024-01-01', '2024-12-31', true, 75000.00);

        $this->assertArrayHasKey('beginning_cash_balance', $result);
        $this->assertArrayHasKey('ending_cash_balance', $result);
        
        $this->assertEquals(75000.00, $result['beginning_cash_balance']);
        // Beginning (75k) + Net change (115k)
        $this->assertEquals(190000.00, $result['ending_cash_balance']);
    }

    public function testGenerateWithEventDispatching(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(100000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturn([]);

        $this->eventDispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->withConsecutive(
                [$this->callback(function ($event) {
                    return $event->getName() === 'reports.cash_flow_statement.before_generate';
                })],
                [$this->callback(function ($event) {
                    return $event->getName() === 'reports.cash_flow_statement.after_generate';
                })]
            );

        $this->report->generate('2024-01-01', '2024-12-31');
    }

    public function testGenerateWithInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('End date must be after start date');

        $this->report->generate('2024-12-31', '2024-01-01');
    }

    public function testGenerateLogsErrors(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->will($this->throwException(new \Exception('Database error')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('Failed to generate cash flow statement'),
                $this->arrayHasKey('exception')
            );

        $this->expectException(\Exception::class);
        $this->report->generate('2024-01-01', '2024-12-31');
    }

    public function testGenerateWithCustomDateFormat(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(100000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturn([]);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('period', $result);
        $this->assertArrayHasKey('start_date', $result['period']);
        $this->assertArrayHasKey('end_date', $result['period']);
        $this->assertEquals('2024-01-01', $result['period']['start_date']);
        $this->assertEquals('2024-12-31', $result['period']['end_date']);
    }

    public function testGenerateCashFlowRatios(): void
    {
        $this->dbal->expects($this->once())
            ->method('fetchOne')
            ->willReturn(100000.00);

        $this->dbal->expects($this->exactly(4))
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                [['total' => 15000.00]],
                [],
                [['activity' => 'Purchase of Equipment', 'amount' => -25000.00]],
                [['activity' => 'Dividend Payments', 'amount' => -10000.00]]
            );

        $result = $this->report->generateMetrics('2024-01-01', '2024-12-31', 50000.00, 200000.00);

        $this->assertArrayHasKey('operating_cash_flow_ratio', $result);
        $this->assertArrayHasKey('cash_return_on_assets', $result);
        $this->assertArrayHasKey('cash_flow_to_debt_ratio', $result);
        
        // Operating CF / Current Liabilities
        $this->assertEquals(2.30, $result['operating_cash_flow_ratio']);
        
        // Operating CF / Total Assets (assuming passed as param)
        $this->assertGreaterThan(0, $result['cash_return_on_assets']);
    }
}
