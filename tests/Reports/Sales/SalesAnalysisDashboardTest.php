<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Sales;

use PHPUnit\Framework\TestCase;
use FA\Modules\Reports\Sales\SalesAnalysisDashboard;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

/**
 * Test suite for Sales Analysis Dashboard Report
 */
class SalesAnalysisDashboardTest extends TestCase
{
    private SalesAnalysisDashboard $report;
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new SalesAnalysisDashboard(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testSalesTotalCalculation(): void
    {
        $mockData = [
            ['month' => '2024-01', 'revenue' => 100000.00, 'orders' => 50, 'customers' => 25],
            ['month' => '2024-02', 'revenue' => 120000.00, 'orders' => 60, 'customers' => 30],
            ['month' => '2024-03', 'revenue' => 150000.00, 'orders' => 75, 'customers' => 35]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-03-31');

        $this->assertArrayHasKey('summary', $result);
        $this->assertEquals(370000.00, $result['summary']['total_revenue']);
        $this->assertEquals(185, $result['summary']['total_orders']);
    }

    public function testAverageOrderValue(): void
    {
        $mockData = [
            ['revenue' => 100000.00, 'orders' => 50]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // AOV = Revenue / Orders = 100000 / 50 = 2000
        $this->assertArrayHasKey('average_order_value', $result['summary']);
        $this->assertEquals(2000.00, $result['summary']['average_order_value']);
    }

    public function testCustomerAcquisition(): void
    {
        $mockData = [
            'new_customers' => 15,
            'returning_customers' => 35,
            'total_customers' => 50
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('customer_metrics', $result);
        $this->assertEquals(15, $result['customer_metrics']['new_customers']);
        $this->assertEquals(35, $result['customer_metrics']['returning_customers']);
        $this->assertEquals(30, $result['customer_metrics']['new_customer_rate']); // 15/50 * 100
    }

    public function testCustomerRetentionRate(): void
    {
        $mockData = [
            'returning_customers' => 40,
            'total_customers' => 50
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Retention rate = 40/50 * 100 = 80%
        $this->assertEquals(80, $result['customer_metrics']['retention_rate']);
    }

    public function testSalesGrowthRate(): void
    {
        $mockData = [
            ['period' => 'current', 'revenue' => 120000.00],
            ['period' => 'previous', 'revenue' => 100000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateGrowthAnalysis('2024-01-01', '2024-12-31');

        // Growth rate = (120000 - 100000) / 100000 * 100 = 20%
        $this->assertArrayHasKey('growth_rate', $result);
        $this->assertEquals(20, round($result['growth_rate']));
    }

    public function testTopProductsByRevenue(): void
    {
        $mockData = [
            ['stock_id' => 'PROD001', 'description' => 'Product A', 'revenue' => 50000.00, 'quantity' => 100],
            ['stock_id' => 'PROD002', 'description' => 'Product B', 'revenue' => 40000.00, 'quantity' => 200],
            ['stock_id' => 'PROD003', 'description' => 'Product C', 'revenue' => 30000.00, 'quantity' => 150]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('top_products', $result);
        $this->assertCount(3, $result['top_products']);
        $this->assertEquals('Product A', $result['top_products'][0]['description']);
        $this->assertEquals(50000.00, $result['top_products'][0]['revenue']);
    }

    public function testTopCustomersByRevenue(): void
    {
        $mockData = [
            ['debtor_no' => '001', 'name' => 'Customer A', 'revenue' => 75000.00, 'orders' => 15],
            ['debtor_no' => '002', 'name' => 'Customer B', 'revenue' => 60000.00, 'orders' => 12],
            ['debtor_no' => '003', 'name' => 'Customer C', 'revenue' => 45000.00, 'orders' => 10]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('top_customers', $result);
        $this->assertCount(3, $result['top_customers']);
        $this->assertEquals('Customer A', $result['top_customers'][0]['name']);
        $this->assertEquals(75000.00, $result['top_customers'][0]['revenue']);
    }

    public function testSalesByRegion(): void
    {
        $mockData = [
            ['region' => 'North', 'revenue' => 100000.00, 'orders' => 50],
            ['region' => 'South', 'revenue' => 80000.00, 'orders' => 40],
            ['region' => 'East', 'revenue' => 90000.00, 'orders' => 45],
            ['region' => 'West', 'revenue' => 70000.00, 'orders' => 35]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateByRegion('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('regions', $result);
        $this->assertCount(4, $result['regions']);
        $this->assertEquals('North', $result['regions'][0]['region']);
        $this->assertEquals(100000.00, $result['regions'][0]['revenue']);
    }

    public function testSalesByCategory(): void
    {
        $mockData = [
            ['category' => 'Electronics', 'revenue' => 150000.00, 'quantity' => 300],
            ['category' => 'Furniture', 'revenue' => 120000.00, 'quantity' => 150],
            ['category' => 'Office Supplies', 'revenue' => 80000.00, 'quantity' => 400]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateByCategory('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(3, $result['categories']);
        $this->assertEquals('Electronics', $result['categories'][0]['category']);
    }

    public function testSalesTrendAnalysis(): void
    {
        $mockData = [
            ['month' => '2024-01', 'revenue' => 100000.00],
            ['month' => '2024-02', 'revenue' => 110000.00],
            ['month' => '2024-03', 'revenue' => 120000.00],
            ['month' => '2024-04', 'revenue' => 125000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-04-30');

        $this->assertArrayHasKey('trend', $result);
        $this->assertEquals('Growing', $result['trend']);
    }

    public function testSeasonalityAnalysis(): void
    {
        $mockData = [
            ['month' => 1, 'avg_revenue' => 100000.00],
            ['month' => 2, 'avg_revenue' => 95000.00],
            ['month' => 3, 'avg_revenue' => 110000.00],
            ['month' => 12, 'avg_revenue' => 150000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateSeasonality('2023-01-01', '2024-12-31');

        $this->assertArrayHasKey('seasonality', $result);
        $this->assertArrayHasKey('peak_month', $result);
        $this->assertEquals(12, $result['peak_month']);
    }

    public function testConversionRate(): void
    {
        $mockData = [
            'quotes' => 100,
            'orders' => 75
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Conversion rate = 75/100 * 100 = 75%
        $this->assertArrayHasKey('conversion_rate', $result['summary']);
        $this->assertEquals(75, $result['summary']['conversion_rate']);
    }

    public function testSalesmanPerformance(): void
    {
        $mockData = [
            ['salesman_id' => 1, 'name' => 'John Doe', 'revenue' => 200000.00, 'orders' => 50, 'customers' => 25],
            ['salesman_id' => 2, 'name' => 'Jane Smith', 'revenue' => 180000.00, 'orders' => 45, 'customers' => 22]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateSalesmanPerformance('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('salespeople', $result);
        $this->assertCount(2, $result['salespeople']);
        $this->assertEquals('John Doe', $result['salespeople'][0]['name']);
        $this->assertEquals(200000.00, $result['salespeople'][0]['revenue']);
    }

    public function testDailySalesAverage(): void
    {
        $mockData = [
            ['revenue' => 365000.00, 'days' => 365]
        ];

        $this->db->method('fetchOne')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Daily average = 365000 / 365 = 1000
        $this->assertArrayHasKey('daily_average', $result['summary']);
        $this->assertEquals(1000.00, $result['summary']['daily_average']);
    }

    public function testYearOverYearComparison(): void
    {
        $mockData = [
            ['year' => 2024, 'revenue' => 500000.00],
            ['year' => 2023, 'revenue' => 400000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateYearOverYear('2023-01-01', '2024-12-31');

        $this->assertArrayHasKey('yoy_growth', $result);
        // (500000 - 400000) / 400000 * 100 = 25%
        $this->assertEquals(25, round($result['yoy_growth']));
    }

    public function testProductMixAnalysis(): void
    {
        $mockData = [
            ['category' => 'Electronics', 'revenue' => 200000.00],
            ['category' => 'Furniture', 'revenue' => 150000.00],
            ['category' => 'Supplies', 'revenue' => 150000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateProductMix('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('product_mix', $result);
        // Electronics = 200k / 500k = 40%
        $this->assertEquals(40, round($result['product_mix'][0]['percentage']));
    }

    public function testCustomerLifetimeValue(): void
    {
        $mockData = [
            ['customer_id' => '001', 'name' => 'Customer A', 'lifetime_revenue' => 50000.00, 'orders' => 20, 'first_order' => '2020-01-01']
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateCustomerLTV('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('top_ltv_customers', $result);
        $this->assertEquals(50000.00, $result['top_ltv_customers'][0]['lifetime_revenue']);
    }

    public function testSalesForecast(): void
    {
        $mockData = [
            ['month' => '2024-01', 'revenue' => 100000.00],
            ['month' => '2024-02', 'revenue' => 105000.00],
            ['month' => '2024-03', 'revenue' => 110000.00]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateForecast('2024-01-01', '2024-03-31', 3);

        $this->assertArrayHasKey('forecast', $result);
        $this->assertArrayHasKey('next_3_months', $result['forecast']);
    }

    public function testExportToPDF(): void
    {
        $mockData = [
            ['month' => '2024-01', 'revenue' => 100000.00, 'orders' => 50, 'customers' => 25]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $pdf = $this->report->exportToPDF($result, 'Sales Analysis Dashboard');

        $this->assertNotEmpty($pdf);
        $this->assertIsString($pdf);
    }

    public function testExportToExcel(): void
    {
        $mockData = [
            ['month' => '2024-01', 'revenue' => 100000.00, 'orders' => 50, 'customers' => 25]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $excel = $this->report->exportToExcel($result, 'Sales Analysis Dashboard');

        $this->assertNotEmpty($excel);
        $this->assertIsString($excel);
    }
}
