<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Sales;

use FA\Modules\Reports\Sales\ProductProfitabilityAnalysis;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test suite for Product Profitability Analysis
 */
class ProductProfitabilityAnalysisTest extends TestCase
{
    private ProductProfitabilityAnalysis $report;
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new ProductProfitabilityAnalysis(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateBasicProfitabilityReport(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('products', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('top_profitable', $result);
        $this->assertArrayHasKey('least_profitable', $result);
        $this->assertNotEmpty($result['products']);
    }

    public function testGrossProfitCalculation(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertEquals(20000.00, $product['gross_profit']); // 50000 - 30000
        $this->assertEquals(40.0, $product['gross_margin_percent']); // (20000 / 50000) * 100
    }

    public function testContributionMarginCalculation(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        // Contribution margin = Revenue - Variable costs (material + labor)
        $this->assertEquals(22000.00, $product['contribution_margin']); // 50000 - 25000 - 3000
        $this->assertEquals(44.0, $product['contribution_margin_percent']);
    }

    public function testProfitPerUnitCalculation(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertEquals(200.00, $product['profit_per_unit']); // 20000 / 100
        $this->assertEquals(500.00, $product['revenue_per_unit']); // 50000 / 100
        $this->assertEquals(300.00, $product['cost_per_unit']); // 30000 / 100
    }

    public function testCostBreakdown(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertArrayHasKey('cost_breakdown', $product);
        $this->assertEquals(83.33, round($product['cost_breakdown']['material_percent'], 2));
        $this->assertEquals(10.0, round($product['cost_breakdown']['labor_percent'], 2));
        $this->assertEquals(6.67, round($product['cost_breakdown']['overhead_percent'], 2));
    }

    public function testTopProfitableProducts(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'High Profit Product',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 100000.00,
                'cost_of_goods' => 40000.00,
                'material_cost' => 35000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Medium Profit Product',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 35000.00,
                'material_cost' => 30000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD003',
                'description' => 'Low Profit Product',
                'category' => 'Electronics',
                'units_sold' => 200,
                'sales_revenue' => 30000.00,
                'cost_of_goods' => 28000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertNotEmpty($result['top_profitable']);
        $this->assertEquals('High Profit Product', $result['top_profitable'][0]['description']);
        $this->assertEquals(60000.00, $result['top_profitable'][0]['gross_profit']);
    }

    public function testLeastProfitableProducts(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Profitable Product',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 20000.00,
                'material_cost' => 15000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Marginally Profitable',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 10000.00,
                'cost_of_goods' => 9500.00,
                'material_cost' => 8000.00,
                'labor_cost' => 1000.00,
                'overhead_cost' => 500.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertNotEmpty($result['least_profitable']);
        // Least profitable should be the first one (lowest profit)
        $leastProfitable = $result['least_profitable'][0];
        $this->assertEquals('Marginally Profitable', $leastProfitable['description']);
        $this->assertLessThan(1000, $leastProfitable['gross_profit']);
    }

    public function testUnprofitableProducts(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Loss Leader',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 10000.00,
                'cost_of_goods' => 12000.00,
                'material_cost' => 10000.00,
                'labor_cost' => 1500.00,
                'overhead_cost' => 500.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertLessThan(0, $product['gross_profit']);
        $this->assertEquals(-2000.00, $product['gross_profit']);
        $this->assertLessThan(0, $product['gross_margin_percent']);
    }

    public function testSummaryMetrics(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Product B',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 25000.00,
                'cost_of_goods' => 15000.00,
                'material_cost' => 12000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $summary = $result['summary'];
        $this->assertEquals(2, $summary['total_products']);
        $this->assertEquals(150, $summary['total_units_sold']);
        $this->assertEquals(75000.00, $summary['total_revenue']);
        $this->assertEquals(45000.00, $summary['total_cost']);
        $this->assertEquals(30000.00, $summary['total_profit']);
        $this->assertEquals(40.0, $summary['overall_margin_percent']);
    }

    public function testByCategory(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Product B',
                'category' => 'Furniture',
                'units_sold' => 50,
                'sales_revenue' => 25000.00,
                'cost_of_goods' => 15000.00,
                'material_cost' => 12000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateByCategory('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('Electronics', $result['categories']);
        $this->assertArrayHasKey('Furniture', $result['categories']);
    }

    public function testProfitabilityTrend(): void
    {
        // Mock trend data (monthly format with 'revenue' not 'sales_revenue')
        $mockTrendData = [
            [
                'month' => '2024-01',
                'units_sold' => 30,
                'revenue' => 15000.00,
                'cost' => 9000.00
            ],
            [
                'month' => '2024-02',
                'units_sold' => 35,
                'revenue' => 17500.00,
                'cost' => 10500.00
            ],
            [
                'month' => '2024-03',
                'units_sold' => 40,
                'revenue' => 20000.00,
                'cost' => 12000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockTrendData);

        $result = $this->report->generateTrend('PROD001');

        $this->assertArrayHasKey('stock_id', $result);
        $this->assertArrayHasKey('monthly_trends', $result);
        $this->assertArrayHasKey('profitability_trend', $result);
        $this->assertEquals('PROD001', $result['stock_id']);
        $this->assertCount(3, $result['monthly_trends']);
    }

    public function testMarginAnalysisByProduct(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'High Margin Product',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 20000.00,
                'material_cost' => 15000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Low Margin Product',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 25000.00,
                'cost_of_goods' => 23000.00,
                'material_cost' => 20000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $highMargin = $result['products'][0];
        $lowMargin = $result['products'][1];

        $this->assertGreaterThan(50, $highMargin['gross_margin_percent']);
        $this->assertLessThan(10, $lowMargin['gross_margin_percent']);
    }

    public function testProductRanking(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Product B',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 40000.00,
                'cost_of_goods' => 15000.00,
                'material_cost' => 12000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        // Products should be sorted by gross profit descending
        $this->assertGreaterThan(
            $result['products'][1]['gross_profit'],
            $result['products'][0]['gross_profit']
        );
    }

    public function testRevenueContribution(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 60000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Product B',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 40000.00,
                'cost_of_goods' => 20000.00,
                'material_cost' => 15000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product1 = $result['products'][0];
        $product2 = $result['products'][1];

        $this->assertEquals(60.0, $product1['revenue_contribution_percent']);
        $this->assertEquals(40.0, $product2['revenue_contribution_percent']);
    }

    public function testProfitContribution(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 20000.00,
                'material_cost' => 15000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ],
            [
                'stock_id' => 'PROD002',
                'description' => 'Product B',
                'category' => 'Electronics',
                'units_sold' => 50,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 40000.00,
                'material_cost' => 35000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product1 = $result['products'][0];
        $product2 = $result['products'][1];

        $this->assertEquals(75.0, $product1['profit_contribution_percent']);
        $this->assertEquals(25.0, $product2['profit_contribution_percent']);
    }

    public function testBreakEvenAnalysis(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertArrayHasKey('break_even_units', $product);
        $this->assertGreaterThan(0, $product['break_even_units']);
    }

    public function testPricingSuggestions(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Low Margin Product',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 30000.00,
                'cost_of_goods' => 28000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 2000.00,
                'overhead_cost' => 1000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $product = $result['products'][0];
        $this->assertArrayHasKey('pricing_recommendations', $product);
        $this->assertArrayHasKey('target_price_30_margin', $product['pricing_recommendations']);
        $this->assertArrayHasKey('target_price_40_margin', $product['pricing_recommendations']);
    }

    public function testExportToPDF(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $pdf = $this->report->exportToPDF($result);

        $this->assertIsString($pdf);
        $this->assertStringContainsString('Product Profitability Analysis', $pdf);
    }

    public function testExportToExcel(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $excel = $this->report->exportToExcel($result);

        $this->assertIsString($excel);
        $this->assertStringContainsString('Product Profitability Analysis', $excel);
    }

    public function testInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->report->generate('2024-12-31', '2024-01-01');
    }

    public function testLoggingOnGeneration(): void
    {
        $mockData = [
            [
                'stock_id' => 'PROD001',
                'description' => 'Product A',
                'category' => 'Electronics',
                'units_sold' => 100,
                'sales_revenue' => 50000.00,
                'cost_of_goods' => 30000.00,
                'material_cost' => 25000.00,
                'labor_cost' => 3000.00,
                'overhead_cost' => 2000.00
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);
        
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Generating Product Profitability Analysis');

        $this->report->generate('2024-01-01', '2024-12-31');
    }

    public function testDatabaseError(): void
    {
        $this->db->method('fetchAll')->willThrowException(
            new \Exception('Database connection failed')
        );

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to generate Product Profitability Analysis', $this->anything());

        $this->expectException(\Exception::class);
        $this->report->generate('2024-01-01', '2024-12-31');
    }
}
