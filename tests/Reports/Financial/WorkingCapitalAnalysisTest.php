<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Financial;

use PHPUnit\Framework\TestCase;
use FA\Modules\Reports\Financial\WorkingCapitalAnalysis;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

/**
 * Test suite for Working Capital Analysis Report
 */
class WorkingCapitalAnalysisTest extends TestCase
{
    private WorkingCapitalAnalysis $report;
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new WorkingCapitalAnalysis(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testCurrentRatioCalculation(): void
    {
        $mockData = [
            [
                'current_assets' => 500000.00,
                'current_liabilities' => 300000.00,
                'cash' => 100000.00,
                'accounts_receivable' => 200000.00,
                'inventory' => 200000.00,
                'accounts_payable' => 150000.00,
                'short_term_debt' => 150000.00
            ]
        ];

        $this->db->method('fetchOne')->willReturn($mockData[0]);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('ratios', $result);
        $this->assertEquals(1.67, round($result['ratios']['current_ratio'], 2));
        $this->assertGreaterThan(1.0, $result['ratios']['current_ratio']);
    }

    public function testQuickRatioCalculation(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'cash' => 100000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Quick ratio = (current assets - inventory) / current liabilities
        // (500000 - 200000) / 300000 = 1.0
        $this->assertArrayHasKey('quick_ratio', $result['ratios']);
        $this->assertEquals(1.0, round($result['ratios']['quick_ratio'], 2));
    }

    public function testCashRatioCalculation(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'cash' => 100000.00,
            'marketable_securities' => 50000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Cash ratio = (cash + marketable securities) / current liabilities
        // (100000 + 50000) / 300000 = 0.5
        $this->assertArrayHasKey('cash_ratio', $result['ratios']);
        $this->assertEquals(0.5, round($result['ratios']['cash_ratio'], 2));
    }

    public function testWorkingCapitalAmount(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('working_capital', $result);
        $this->assertEquals(200000.00, $result['working_capital']);
        $this->assertGreaterThan(0, $result['working_capital']);
    }

    public function testDaysWorkingCapital(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'annual_revenue' => 3650000.00 // $10k per day
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Days WC = (Working Capital / Annual Revenue) * 365
        // (200000 / 3650000) * 365 = 20 days
        $this->assertArrayHasKey('days_working_capital', $result['metrics']);
        $this->assertEquals(20, round($result['metrics']['days_working_capital']));
    }

    public function testDaysSalesOutstanding(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'accounts_receivable' => 200000.00,
            'annual_revenue' => 3650000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // DSO = (AR / Annual Revenue) * 365
        // (200000 / 3650000) * 365 = 20 days
        $this->assertArrayHasKey('days_sales_outstanding', $result['metrics']);
        $this->assertEquals(20, round($result['metrics']['days_sales_outstanding']));
    }

    public function testDaysInventoryOutstanding(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'inventory' => 200000.00,
            'cost_of_goods_sold' => 2190000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // DIO = (Inventory / COGS) * 365
        // (200000 / 2190000) * 365 = 33.3 days
        $this->assertArrayHasKey('days_inventory_outstanding', $result['metrics']);
        $this->assertEquals(33, round($result['metrics']['days_inventory_outstanding']));
    }

    public function testDaysPayableOutstanding(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'accounts_payable' => 150000.00,
            'cost_of_goods_sold' => 2190000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // DPO = (AP / COGS) * 365
        // (150000 / 2190000) * 365 = 25 days
        $this->assertArrayHasKey('days_payable_outstanding', $result['metrics']);
        $this->assertEquals(25, round($result['metrics']['days_payable_outstanding']));
    }

    public function testCashConversionCycle(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00,
            'accounts_payable' => 150000.00,
            'annual_revenue' => 3650000.00,
            'cost_of_goods_sold' => 2190000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // CCC = DSO + DIO - DPO
        // DSO = 20, DIO = 33, DPO = 25
        // CCC = 20 + 33 - 25 = 28 days
        $this->assertArrayHasKey('cash_conversion_cycle', $result['metrics']);
        $this->assertEquals(28, round($result['metrics']['cash_conversion_cycle']));
    }

    public function testWorkingCapitalTurnover(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'annual_revenue' => 3650000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // WC Turnover = Revenue / Working Capital
        // 3650000 / 200000 = 18.25
        $this->assertArrayHasKey('working_capital_turnover', $result['metrics']);
        $this->assertEquals(18.25, round($result['metrics']['working_capital_turnover'], 2));
    }

    public function testWorkingCapitalRatio(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'total_assets' => 1000000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // WC Ratio = Working Capital / Total Assets
        // 200000 / 1000000 = 0.2 (20%)
        $this->assertArrayHasKey('working_capital_ratio', $result['metrics']);
        $this->assertEquals(0.2, round($result['metrics']['working_capital_ratio'], 2));
    }

    public function testHealthyWorkingCapitalDetection(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('health_status', $result);
        // Current ratio 1.67 is healthy (> 1.5)
        $this->assertEquals('Healthy', $result['health_status']);
    }

    public function testCautionWorkingCapitalDetection(): void
    {
        $mockData = [
            'current_assets' => 350000.00,
            'current_liabilities' => 300000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Current ratio 1.17 is caution (1.0 - 1.5)
        $this->assertEquals('Caution', $result['health_status']);
    }

    public function testCriticalWorkingCapitalDetection(): void
    {
        $mockData = [
            'current_assets' => 250000.00,
            'current_liabilities' => 300000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Current ratio 0.83 is critical (< 1.0)
        $this->assertEquals('Critical', $result['health_status']);
    }

    public function testTrendAnalysis(): void
    {
        $this->db->method('fetchAll')->willReturn([
            ['period' => '2024-Q1', 'current_ratio' => 1.5, 'working_capital' => 150000],
            ['period' => '2024-Q2', 'current_ratio' => 1.6, 'working_capital' => 180000],
            ['period' => '2024-Q3', 'current_ratio' => 1.7, 'working_capital' => 200000],
            ['period' => '2024-Q4', 'current_ratio' => 1.8, 'working_capital' => 220000]
        ]);

        $result = $this->report->generateTrend('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('trends', $result);
        $this->assertCount(4, $result['trends']);
        $this->assertEquals('Improving', $result['trend_direction']);
    }

    public function testComponentBreakdown(): void
    {
        $mockData = [
            'cash' => 100000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00,
            'other_current_assets' => 0,
            'accounts_payable' => 150000.00,
            'short_term_debt' => 150000.00,
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('components', $result);
        $this->assertArrayHasKey('assets', $result['components']);
        $this->assertArrayHasKey('liabilities', $result['components']);
        
        // Check asset percentages
        $this->assertEquals(20, round($result['components']['assets']['cash_percent']));
        $this->assertEquals(40, round($result['components']['assets']['ar_percent']));
        $this->assertEquals(40, round($result['components']['assets']['inventory_percent']));
        
        // Check liability percentages
        $this->assertEquals(50, round($result['components']['liabilities']['ap_percent']));
        $this->assertEquals(50, round($result['components']['liabilities']['debt_percent']));
    }

    public function testBenchmarkComparison(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'cash' => 100000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generateBenchmarks('2024-01-01', '2024-12-31', 'manufacturing');

        $this->assertArrayHasKey('company_ratios', $result);
        $this->assertArrayHasKey('industry_benchmarks', $result);
        $this->assertArrayHasKey('comparison', $result);
        
        // Check if comparison shows above/below benchmark
        $this->assertArrayHasKey('current_ratio_vs_benchmark', $result['comparison']);
    }

    public function testEfficiencyScoreCalculation(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00,
            'accounts_payable' => 150000.00,
            'annual_revenue' => 3650000.00,
            'cost_of_goods_sold' => 2190000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('efficiency_score', $result);
        $this->assertGreaterThanOrEqual(0, $result['efficiency_score']);
        $this->assertLessThanOrEqual(100, $result['efficiency_score']);
    }

    public function testRecommendations(): void
    {
        $mockData = [
            'current_assets' => 250000.00, // Low current ratio
            'current_liabilities' => 300000.00,
            'accounts_receivable' => 150000.00, // High DSO
            'inventory' => 100000.00,
            'accounts_payable' => 50000.00, // Low DPO
            'annual_revenue' => 1825000.00,
            'cost_of_goods_sold' => 1095000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('recommendations', $result);
        $this->assertNotEmpty($result['recommendations']);
        
        // Should have recommendations for low current ratio, high DSO, etc.
        $this->assertGreaterThan(0, count($result['recommendations']));
    }

    public function testExportToPDF(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'cash' => 100000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00,
            'accounts_payable' => 150000.00,
            'short_term_debt' => 150000.00,
            'annual_revenue' => 3650000.00,
            'cost_of_goods_sold' => 2190000.00,
            'total_assets' => 1000000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $pdf = $this->report->exportToPDF($result, 'Working Capital Analysis');

        $this->assertNotEmpty($pdf);
        $this->assertIsString($pdf);
    }

    public function testExportToExcel(): void
    {
        $mockData = [
            'current_assets' => 500000.00,
            'current_liabilities' => 300000.00,
            'cash' => 100000.00,
            'accounts_receivable' => 200000.00,
            'inventory' => 200000.00,
            'accounts_payable' => 150000.00,
            'short_term_debt' => 150000.00,
            'annual_revenue' => 3650000.00,
            'cost_of_goods_sold' => 2190000.00,
            'total_assets' => 1000000.00
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $excel = $this->report->exportToExcel($result, 'Working Capital Analysis');

        $this->assertNotEmpty($excel);
        $this->assertIsString($excel);
    }
}
