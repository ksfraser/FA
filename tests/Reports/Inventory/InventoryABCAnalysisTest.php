<?php

declare(strict_types=1);

namespace FA\Tests\Reports\Inventory;

use FA\Modules\Reports\Inventory\InventoryABCAnalysisReport;
use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test suite for Inventory ABC Analysis Report
 * 
 * Tests Pareto principle application, classification algorithms,
 * value analysis, and inventory optimization recommendations.
 * 
 * @covers \FA\Modules\Reports\Inventory\InventoryABCAnalysisReport
 */
class InventoryABCAnalysisTest extends TestCase
{
    private InventoryABCAnalysisReport $report;
    private DBALInterface $db;
    private EventDispatcher $dispatcher;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->db = $this->createMock(DBALInterface::class);
        $this->dispatcher = $this->createMock(EventDispatcher::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->report = new InventoryABCAnalysisReport(
            $this->db,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGenerateBasicABCAnalysis(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'High Value Item', 'quantity' => 100, 'unit_cost' => 1000.00, 'annual_usage' => 50],
            ['item_code' => 'ITEM002', 'description' => 'Medium Value Item', 'quantity' => 200, 'unit_cost' => 500.00, 'annual_usage' => 40],
            ['item_code' => 'ITEM003', 'description' => 'Low Value Item', 'quantity' => 500, 'unit_cost' => 50.00, 'annual_usage' => 100],
            ['item_code' => 'ITEM004', 'description' => 'Very Low Value', 'quantity' => 1000, 'unit_cost' => 10.00, 'annual_usage' => 200],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('items', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('classification', $result);
        $this->assertArrayHasKey('recommendations', $result);
    }

    public function testABCClassificationByValue(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'High', 'quantity' => 10, 'unit_cost' => 10000.00, 'annual_usage' => 100],
            ['item_code' => 'ITEM002', 'description' => 'Medium', 'quantity' => 50, 'unit_cost' => 2000.00, 'annual_usage' => 50],
            ['item_code' => 'ITEM003', 'description' => 'Low', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        // Class A should have highest value items (70-80% of value)
        $classA = array_filter($result['items'], fn($item) => $item['abc_class'] === 'A');
        $classB = array_filter($result['items'], fn($item) => $item['abc_class'] === 'B');
        $classC = array_filter($result['items'], fn($item) => $item['abc_class'] === 'C');

        $this->assertNotEmpty($classA);
        $this->assertGreaterThan(0, count($classA));
    }

    public function testParetoAnalysis(): void
    {
        $mockInventory = [];
        for ($i = 1; $i <= 100; $i++) {
            $mockInventory[] = [
                'item_code' => sprintf('ITEM%03d', $i),
                'description' => "Item $i",
                'quantity' => 100,
                'unit_cost' => (101 - $i) * 10, // Decreasing values
                'annual_usage' => 50
            ];
        }

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        // Verify Pareto principle: ~20% of items should account for ~80% of value
        $totalValue = $result['summary']['total_value'];
        $classAValue = $result['classification']['class_a']['total_value'];
        
        $classAPercentValue = ($classAValue / $totalValue) * 100;
        $this->assertGreaterThanOrEqual(70, $classAPercentValue);
        $this->assertLessThanOrEqual(85, $classAPercentValue);
    }

    public function testClassificationThresholds(): void
    {
        $mockInventory = [
            ['item_code' => 'A1', 'description' => 'A Item', 'quantity' => 10, 'unit_cost' => 1000.00, 'annual_usage' => 100],
            ['item_code' => 'B1', 'description' => 'B Item', 'quantity' => 20, 'unit_cost' => 400.00, 'annual_usage' => 50],
            ['item_code' => 'C1', 'description' => 'C Item', 'quantity' => 50, 'unit_cost' => 20.00, 'annual_usage' => 20],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate(['class_a_threshold' => 80, 'class_b_threshold' => 95]);

        $this->assertArrayHasKey('classification', $result);
        $this->assertArrayHasKey('class_a', $result['classification']);
        $this->assertArrayHasKey('class_b', $result['classification']);
        $this->assertArrayHasKey('class_c', $result['classification']);
    }

    public function testAnnualValueCalculation(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'Test Item', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 120],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $item = $result['items'][0];
        $expectedAnnualValue = 50.00 * 120; // unit_cost * annual_usage
        $this->assertEquals($expectedAnnualValue, $item['annual_value']);
    }

    public function testInventoryTurnoverCalculation(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'Fast Mover', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 1200],
            ['item_code' => 'ITEM002', 'description' => 'Slow Mover', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 12],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        // Fast mover: 1200 / 100 = 12 turns per year
        $this->assertEquals(12.0, $result['items'][0]['turnover_ratio']);
        
        // Slow mover: 12 / 100 = 0.12 turns per year
        $this->assertEquals(0.12, $result['items'][1]['turnover_ratio']);
    }

    public function testRecommendationsForClassA(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'High Value', 'quantity' => 10, 'unit_cost' => 10000.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $classARecommendations = $result['recommendations']['class_a'];
        
        $this->assertStringContainsString('tight control', strtolower($classARecommendations));
        $this->assertStringContainsString('frequent review', strtolower($classARecommendations));
    }

    public function testCumulativeValueCalculation(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'Item 1', 'quantity' => 10, 'unit_cost' => 1000.00, 'annual_usage' => 10],
            ['item_code' => 'ITEM002', 'description' => 'Item 2', 'quantity' => 10, 'unit_cost' => 500.00, 'annual_usage' => 10],
            ['item_code' => 'ITEM003', 'description' => 'Item 3', 'quantity' => 10, 'unit_cost' => 100.00, 'annual_usage' => 10],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        // Items should be sorted by value descending
        $this->assertGreaterThan($result['items'][1]['annual_value'], $result['items'][0]['annual_value']);
        
        // Cumulative percentages should increase
        $this->assertLessThan($result['items'][1]['cumulative_percent'], $result['items'][0]['cumulative_percent']);
    }

    public function testSummaryStatistics(): void
    {
        $mockInventory = [
            ['item_code' => 'A', 'description' => 'A', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 50],
            ['item_code' => 'B', 'description' => 'B', 'quantity' => 200, 'unit_cost' => 50.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $summary = $result['summary'];
        $this->assertArrayHasKey('total_items', $summary);
        $this->assertArrayHasKey('total_value', $summary);
        $this->assertArrayHasKey('average_value', $summary);
        $this->assertEquals(2, $summary['total_items']);
    }

    public function testByCategory(): void
    {
        $mockInventory = [
            ['item_code' => 'A', 'description' => 'A', 'category' => 'CAT1', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 50],
            ['item_code' => 'B', 'description' => 'B', 'category' => 'CAT2', 'quantity' => 200, 'unit_cost' => 50.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generateByCategory();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
    }

    public function testByLocation(): void
    {
        $mockInventory = [
            ['item_code' => 'A', 'description' => 'A', 'location' => 'WH1', 'quantity' => 100, 'unit_cost' => 100.00, 'annual_usage' => 50],
            ['item_code' => 'B', 'description' => 'B', 'location' => 'WH2', 'quantity' => 200, 'unit_cost' => 50.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generateByLocation();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('locations', $result);
    }

    public function testSlowMovingIdentification(): void
    {
        $mockInventory = [
            ['item_code' => 'SLOW', 'description' => 'Slow Mover', 'quantity' => 1000, 'unit_cost' => 50.00, 'annual_usage' => 10],
            ['item_code' => 'FAST', 'description' => 'Fast Mover', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 1000],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $slowItem = array_values(array_filter($result['items'], fn($i) => $i['item_code'] === 'SLOW'))[0];
        $this->assertTrue($slowItem['is_slow_moving'] ?? false);
        $this->assertLessThan(1.0, $slowItem['turnover_ratio']);
    }

    public function testObsoleteInventoryDetection(): void
    {
        $mockInventory = [
            ['item_code' => 'OBSOLETE', 'description' => 'No Movement', 'quantity' => 500, 'unit_cost' => 100.00, 'annual_usage' => 0],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $item = $result['items'][0];
        $this->assertTrue($item['is_obsolete'] ?? false);
        $this->assertEquals(0, $item['annual_usage']);
    }

    public function testReorderPointRecommendations(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'Active Item', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 365],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();

        $item = $result['items'][0];
        $this->assertArrayHasKey('recommended_reorder_point', $item);
        $this->assertGreaterThan(0, $item['recommended_reorder_point']);
    }

    public function testSafetyStockCalculation(): void
    {
        $mockInventory = [
            ['item_code' => 'ITEM001', 'description' => 'Item', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 365],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate(['lead_time_days' => 14, 'service_level' => 0.95]);

        $item = $result['items'][0];
        $this->assertArrayHasKey('recommended_safety_stock', $item);
    }

    public function testExportToPDF(): void
    {
        $mockInventory = [
            ['item_code' => 'TEST', 'description' => 'Test', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();
        $pdf = $this->report->exportToPDF($result);

        $this->assertIsString($pdf);
        $this->assertStringContainsString('ABC Analysis', $pdf);
    }

    public function testExportToExcel(): void
    {
        $mockInventory = [
            ['item_code' => 'TEST', 'description' => 'Test', 'quantity' => 100, 'unit_cost' => 50.00, 'annual_usage' => 100],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();
        $excel = $this->report->exportToExcel($result);

        $this->assertIsString($excel);
        $this->assertNotEmpty($excel);
    }

    public function testParetoChart(): void
    {
        $mockInventory = [];
        for ($i = 1; $i <= 20; $i++) {
            $mockInventory[] = [
                'item_code' => "ITEM$i",
                'description' => "Item $i",
                'quantity' => 100,
                'unit_cost' => (21 - $i) * 10,
                'annual_usage' => 50
            ];
        }

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate();
        $chart = $this->report->generateParetoChart($result);

        $this->assertIsArray($chart);
        $this->assertArrayHasKey('labels', $chart);
        $this->assertArrayHasKey('values', $chart);
        $this->assertArrayHasKey('cumulative', $chart);
    }

    public function testInvalidDataHandling(): void
    {
        $this->db->method('fetchAll')->willReturn([]);

        $result = $this->report->generate();

        $this->assertIsArray($result);
        $this->assertEmpty($result['items']);
    }

    public function testLoggingOnGeneration(): void
    {
        $this->logger->expects($this->atLeastOnce())
            ->method('info');

        $this->db->method('fetchAll')->willReturn([]);

        $this->report->generate();
    }

    public function testDatabaseError(): void
    {
        $this->db->method('fetchAll')
            ->willThrowException(new \Exception('Database error'));

        $this->logger->expects($this->once())
            ->method('error');

        $this->expectException(\Exception::class);
        $this->report->generate();
    }

    public function testCustomThresholds(): void
    {
        $mockInventory = [
            ['item_code' => 'A', 'description' => 'A', 'quantity' => 10, 'unit_cost' => 1000.00, 'annual_usage' => 10],
            ['item_code' => 'B', 'description' => 'B', 'quantity' => 10, 'unit_cost' => 500.00, 'annual_usage' => 10],
            ['item_code' => 'C', 'description' => 'C', 'quantity' => 10, 'unit_cost' => 100.00, 'annual_usage' => 10],
        ];

        $this->db->method('fetchAll')->willReturn($mockInventory);

        $result = $this->report->generate([
            'class_a_threshold' => 70,  // A items: top 70% of value
            'class_b_threshold' => 90   // B items: 70-90% of value
        ]);

        $this->assertIsArray($result['classification']);
    }
}
