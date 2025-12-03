<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Purchasing;

use FA\Modules\Reports\Purchasing\SupplierPerformanceDashboard;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test suite for Supplier Performance Dashboard
 */
class SupplierPerformanceDashboardTest extends TestCase
{
    private SupplierPerformanceDashboard $report;
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new SupplierPerformanceDashboard(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateBasicDashboard(): void
    {
        $mockSupplierData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'ABC Supplies Inc',
                'total_orders' => 50,
                'total_value' => 125000.00,
                'on_time_deliveries' => 45,
                'late_deliveries' => 5,
                'quality_issues' => 2,
                'avg_lead_time' => 14.5,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockSupplierData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('suppliers', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('top_performers', $result);
        $this->assertArrayHasKey('underperformers', $result);
        $this->assertArrayHasKey('metrics', $result);
        $this->assertNotEmpty($result['suppliers']);
    }

    public function testOnTimeDeliveryRate(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Reliable Supplier',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 95,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 10.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertEquals(95.0, $supplier['on_time_delivery_rate']);
        $this->assertEquals('Excellent', $supplier['delivery_rating']);
    }

    public function testQualityScore(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Quality Supplier',
                'total_orders' => 100,
                'total_value' => 300000.00,
                'on_time_deliveries' => 85,
                'late_deliveries' => 15,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertArrayHasKey('quality_score', $supplier);
        $this->assertEquals(98.0, $supplier['quality_score']); // (100 - 2) orders without issues
        $this->assertGreaterThanOrEqual(90, $supplier['quality_score']);
    }

    public function testLeadTimeAnalysis(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Fast Supplier',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 48,
                'late_deliveries' => 2,
                'quality_issues' => 0,
                'avg_lead_time' => 7.5,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Slow Supplier',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 40,
                'late_deliveries' => 10,
                'quality_issues' => 1,
                'avg_lead_time' => 25.0,
                'payment_terms' => 'Net 60'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertEquals(7.5, $result['suppliers'][0]['avg_lead_time']);
        $this->assertEquals(25.0, $result['suppliers'][1]['avg_lead_time']);
        
        // Average lead time should be in summary
        $avgLeadTime = array_sum(array_column($result['suppliers'], 'avg_lead_time')) / count($result['suppliers']);
        $this->assertEquals(16.25, $avgLeadTime);
    }

    public function testOverallPerformanceScore(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Excellent Supplier',
                'total_orders' => 100,
                'total_value' => 500000.00,
                'on_time_deliveries' => 95,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 10.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertArrayHasKey('overall_score', $supplier);
        $this->assertGreaterThanOrEqual(90, $supplier['overall_score']);
        $this->assertEquals('A', $supplier['performance_grade']);
    }

    public function testPriceCompetitiveness(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 45,
                'late_deliveries' => 5,
                'quality_issues' => 0,
                'avg_lead_time' => 14.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertArrayHasKey('avg_order_value', $supplier);
        $this->assertEquals(2000.00, $supplier['avg_order_value']); // 100000 / 50
    }

    public function testTopPerformers(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Best Supplier',
                'total_orders' => 100,
                'total_value' => 500000.00,
                'on_time_deliveries' => 98,
                'late_deliveries' => 2,
                'quality_issues' => 0,
                'avg_lead_time' => 8.0,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Good Supplier',
                'total_orders' => 80,
                'total_value' => 300000.00,
                'on_time_deliveries' => 75,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 45'
            ],
            [
                'supplier_id' => '3',
                'supplier_name' => 'Poor Supplier',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 30,
                'late_deliveries' => 20,
                'quality_issues' => 5,
                'avg_lead_time' => 30.0,
                'payment_terms' => 'Net 60'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('top_performers', $result);
        $this->assertNotEmpty($result['top_performers']);
        
        // Top performer should be 'Best Supplier'
        $topPerformer = $result['top_performers'][0];
        $this->assertEquals('Best Supplier', $topPerformer['supplier_name']);
        $this->assertGreaterThanOrEqual(90, $topPerformer['overall_score']);
    }

    public function testUnderperformers(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Excellent Supplier',
                'total_orders' => 100,
                'total_value' => 500000.00,
                'on_time_deliveries' => 95,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 10.0,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Problematic Supplier',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 25,
                'late_deliveries' => 25,
                'quality_issues' => 10,
                'avg_lead_time' => 35.0,
                'payment_terms' => 'Net 60'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('underperformers', $result);
        $this->assertNotEmpty($result['underperformers']);
        
        // Underperformer should be 'Problematic Supplier'
        $underperformers = array_values($result['underperformers']); // Re-index
        $underperformer = $underperformers[0];
        $this->assertEquals('Problematic Supplier', $underperformer['supplier_name']);
        $this->assertLessThan(70, $underperformer['overall_score']);
    }

    public function testSummaryMetrics(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Supplier B',
                'total_orders' => 50,
                'total_value' => 100000.00,
                'on_time_deliveries' => 45,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 15.0,
                'payment_terms' => 'Net 45'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $summary = $result['summary'];
        $this->assertEquals(2, $summary['total_suppliers']);
        $this->assertEquals(150, $summary['total_orders']);
        $this->assertEquals(300000.00, $summary['total_value']);
        $this->assertEquals(90.0, $summary['overall_on_time_rate']); // (90+45)/(100+50) * 100
        $this->assertEquals(98.0, $summary['overall_quality_score']); // ((100-2)+(50-1))/(100+50) * 100
    }

    public function testByCategory(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Raw Materials Supplier',
                'category' => 'Raw Materials',
                'total_orders' => 50,
                'total_value' => 150000.00,
                'on_time_deliveries' => 45,
                'late_deliveries' => 5,
                'quality_issues' => 1,
                'avg_lead_time' => 10.0,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Packaging Supplier',
                'category' => 'Packaging',
                'total_orders' => 30,
                'total_value' => 50000.00,
                'on_time_deliveries' => 28,
                'late_deliveries' => 2,
                'quality_issues' => 0,
                'avg_lead_time' => 7.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateByCategory('2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('Raw Materials', $result['categories']);
        $this->assertArrayHasKey('Packaging', $result['categories']);
    }

    public function testTrendAnalysis(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generateTrends('1');

        $this->assertArrayHasKey('supplier_id', $result);
        $this->assertArrayHasKey('monthly_trends', $result);
        $this->assertArrayHasKey('performance_trend', $result);
    }

    public function testCostAnalysis(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertArrayHasKey('total_value', $supplier);
        $this->assertArrayHasKey('avg_order_value', $supplier);
        $this->assertEquals(200000.00, $supplier['total_value']);
        $this->assertEquals(2000.00, $supplier['avg_order_value']);
    }

    public function testComparisonReport(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ],
            [
                'supplier_id' => '2',
                'supplier_name' => 'Supplier B',
                'total_orders' => 80,
                'total_value' => 150000.00,
                'on_time_deliveries' => 70,
                'late_deliveries' => 10,
                'quality_issues' => 3,
                'avg_lead_time' => 15.0,
                'payment_terms' => 'Net 45'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->compareSuppliers(['1', '2'], '2024-01-01', '2024-12-31');

        $this->assertArrayHasKey('comparison', $result);
        $this->assertCount(2, $result['comparison']);
        $this->assertArrayHasKey('winner', $result);
        $this->assertEquals('Supplier A', $result['winner']['supplier_name']);
    }

    public function testRiskAssessment(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'High Risk Supplier',
                'total_orders' => 50,
                'total_value' => 500000.00, // High dependency
                'on_time_deliveries' => 30,  // Poor delivery
                'late_deliveries' => 20,
                'quality_issues' => 8,       // Many quality issues
                'avg_lead_time' => 35.0,     // Long lead time
                'payment_terms' => 'Net 60'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');

        $supplier = $result['suppliers'][0];
        $this->assertArrayHasKey('risk_level', $supplier);
        $this->assertEquals('High', $supplier['risk_level']);
        $this->assertArrayHasKey('risk_factors', $supplier);
        $this->assertContains('Poor on-time delivery', $supplier['risk_factors']);
        $this->assertContains('High quality issues', $supplier['risk_factors']);
    }

    public function testExportToPDF(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $pdf = $this->report->exportToPDF($result);

        $this->assertIsString($pdf);
        $this->assertStringContainsString('Supplier Performance Dashboard', $pdf);
    }

    public function testExportToExcel(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);

        $result = $this->report->generate('2024-01-01', '2024-12-31');
        $excel = $this->report->exportToExcel($result);

        $this->assertIsString($excel);
        $this->assertStringContainsString('Supplier Performance Dashboard', $excel);
    }

    public function testInvalidDateRange(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->report->generate('2024-12-31', '2024-01-01'); // End before start
    }

    public function testLoggingOnGeneration(): void
    {
        $mockData = [
            [
                'supplier_id' => '1',
                'supplier_name' => 'Supplier A',
                'total_orders' => 100,
                'total_value' => 200000.00,
                'on_time_deliveries' => 90,
                'late_deliveries' => 10,
                'quality_issues' => 2,
                'avg_lead_time' => 12.0,
                'payment_terms' => 'Net 30'
            ]
        ];

        $this->db->method('fetchAll')->willReturn($mockData);
        
        $this->logger->expects($this->once())
            ->method('info')
            ->with('Generating Supplier Performance Dashboard');

        $this->report->generate('2024-01-01', '2024-12-31');
    }

    public function testDatabaseError(): void
    {
        $this->db->method('fetchAll')->willThrowException(
            new \Exception('Database connection failed')
        );

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to generate Supplier Performance Dashboard', $this->anything());

        $this->expectException(\Exception::class);
        $this->report->generate('2024-01-01', '2024-12-31');
    }
}
