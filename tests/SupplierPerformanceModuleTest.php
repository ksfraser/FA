<?php
/**
 * FrontAccounting Supplier Performance Module Tests
 *
 * Unit tests for supplier performance functionality.
 *
 * @package FA\Modules\SupplierPerformance
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\SupplierPerformance;

// Temporary fix for autoload issue
require_once __DIR__ . '/../modules/SupplierPerformance/Entities.php';
require_once __DIR__ . '/../modules/SupplierPerformance/Events.php';
require_once __DIR__ . '/../modules/SupplierPerformance/SupplierPerformanceException.php';

use PHPUnit\Framework\TestCase;
use FA\Modules\SupplierPerformance\SupplierPerformanceService;
use FA\Modules\SupplierPerformance\Entities\SupplierEvaluation;
use FA\Modules\SupplierPerformance\Entities\SupplierMetric;
use FA\Modules\SupplierPerformance\Entities\SupplierRating;
use FA\Modules\SupplierPerformance\Events\SupplierEvaluationCreatedEvent;
use FA\Modules\SupplierPerformance\Events\SupplierRatingUpdatedEvent;
use FA\Modules\SupplierPerformance\SupplierPerformanceException;
use FA\Modules\SupplierPerformance\SupplierPerformanceValidationException;
use FA\Modules\SupplierPerformance\SupplierEvaluationNotFoundException;

/**
 * Supplier Performance Module Test Suite
 */
class SupplierPerformanceModuleTest extends TestCase
{
    private SupplierPerformanceService $service;
    private $mockDBAL;
    private $mockEventDispatcher;
    private $mockLogger;

    protected function setUp(): void
    {
        $this->mockDBAL = $this->createMock(\FA\Database\DBALInterface::class);
        $this->mockEventDispatcher = $this->createMock(\Psr\EventDispatcher\EventDispatcherInterface::class);
        $this->mockLogger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->service = new SupplierPerformanceService(
            $this->mockDBAL,
            $this->mockEventDispatcher,
            $this->mockLogger
        );
    }

    /**
     * Test creating a supplier evaluation
     */
    public function testCreateSupplierEvaluation(): void
    {
        $evaluationData = [
            'supplier_id' => 123,
            'evaluator_id' => 5,
            'period_start' => '2025-01-01',
            'period_end' => '2025-03-31',
            'quality_score' => 85.0,
            'delivery_score' => 90.0,
            'price_score' => 80.0,
            'service_score' => 88.0,
            'compliance_score' => 95.0
        ];

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['count' => 0]);

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('supplier_evaluations', $this->callback(function($data) {
                return isset($data['evaluation_reference']) &&
                       $data['supplier_id'] == 123 &&
                       $data['overall_score'] > 0;
            }))
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SupplierEvaluationCreatedEvent::class));

        $evaluation = $this->service->createEvaluation($evaluationData);

        $this->assertInstanceOf(SupplierEvaluation::class, $evaluation);
        $this->assertEquals(123, $evaluation->getSupplierId());
        $this->assertEquals('draft', $evaluation->getStatus());
        $this->assertGreaterThan(0, $evaluation->getOverallScore());
    }

    /**
     * Test finalizing an evaluation
     */
    public function testFinalizeEvaluation(): void
    {
        $evaluationId = 1;

        $this->mockDBAL->expects($this->exactly(3))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1,
                    'evaluation_reference' => 'SPE-2025-0001',
                    'supplier_id' => 123,
                    'status' => 'draft',
                    'overall_score' => 87.5,
                    'evaluation_date' => '2025-11-30',
                    'evaluator_id' => 5,
                    'evaluation_period_start' => '2025-01-01',
                    'evaluation_period_end' => '2025-03-31',
                    'created_at' => '2025-11-30 10:00:00',
                    'updated_at' => '2025-11-30 10:00:00'
                ],
                null,
                [
                    'id' => 1,
                    'evaluation_reference' => 'SPE-2025-0001',
                    'supplier_id' => 123,
                    'status' => 'finalized',
                    'overall_score' => 87.5,
                    'finalized_at' => date('Y-m-d H:i:s'),
                    'evaluation_date' => '2025-11-30',
                    'evaluator_id' => 5,
                    'evaluation_period_start' => '2025-01-01',
                    'evaluation_period_end' => '2025-03-31',
                    'created_at' => '2025-11-30 10:00:00',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );

        $this->mockDBAL->expects($this->exactly(2))
            ->method('update');

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SupplierRatingUpdatedEvent::class));

        $evaluation = $this->service->finalizeEvaluation($evaluationId);

        $this->assertEquals('finalized', $evaluation->getStatus());
        $this->assertNotNull($evaluation->getFinalizedAt());
    }

    /**
     * Test tracking a supplier metric
     */
    public function testTrackSupplierMetric(): void
    {
        $metricData = [
            'supplier_id' => 123,
            'metric_type' => 'on_time_delivery',
            'metric_value' => 95.5,
            'target_value' => 90.0,
            'unit' => 'percentage',
            'period' => 'monthly'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('supplier_metrics', $this->callback(function($data) use ($metricData) {
                return $data['supplier_id'] == $metricData['supplier_id'] &&
                       $data['metric_type'] === $metricData['metric_type'] &&
                       $data['metric_value'] == $metricData['metric_value'];
            }))
            ->willReturn(1);

        $metric = $this->service->trackMetric($metricData);

        $this->assertInstanceOf(SupplierMetric::class, $metric);
        $this->assertEquals(123, $metric->getSupplierId());
        $this->assertEquals('on_time_delivery', $metric->getMetricType());
        $this->assertEquals(95.5, $metric->getMetricValue());
    }

    /**
     * Test updating supplier rating
     */
    public function testUpdateSupplierRating(): void
    {
        $supplierId = 123;
        $score = 87.5;

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('supplier_ratings', $this->callback(function($data) use ($supplierId, $score) {
                return $data['supplier_id'] == $supplierId &&
                       $data['current_score'] == $score &&
                       $data['rating'] === 'good';
            }))
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(SupplierRatingUpdatedEvent::class));

        $rating = $this->service->updateSupplierRating($supplierId, $score);

        $this->assertInstanceOf(SupplierRating::class, $rating);
        $this->assertEquals($score, $rating->getCurrentScore());
        $this->assertEquals('good', $rating->getRating());
    }

    /**
     * Test getting supplier evaluations
     */
    public function testGetSupplierEvaluations(): void
    {
        $supplierId = 123;

        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'evaluation_reference' => 'SPE-2025-0001',
                    'supplier_id' => 123,
                    'overall_score' => 87.5,
                    'status' => 'finalized'
                ],
                [
                    'id' => 2,
                    'evaluation_reference' => 'SPE-2025-0002',
                    'supplier_id' => 123,
                    'overall_score' => 90.0,
                    'status' => 'finalized'
                ]
            ]);

        $evaluations = $this->service->getSupplierEvaluations($supplierId);

        $this->assertCount(2, $evaluations);
        $this->assertInstanceOf(SupplierEvaluation::class, $evaluations[0]);
        $this->assertEquals(87.5, $evaluations[0]->getOverallScore());
    }

    /**
     * Test getting supplier metrics
     */
    public function testGetSupplierMetrics(): void
    {
        $supplierId = 123;

        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'supplier_id' => 123,
                    'metric_type' => 'on_time_delivery',
                    'metric_value' => 95.5,
                    'metric_date' => '2025-11-01'
                ],
                [
                    'id' => 2,
                    'supplier_id' => 123,
                    'metric_type' => 'quality_defects',
                    'metric_value' => 2.5,
                    'metric_date' => '2025-11-01'
                ]
            ]);

        $metrics = $this->service->getSupplierMetrics($supplierId);

        $this->assertCount(2, $metrics);
        $this->assertInstanceOf(SupplierMetric::class, $metrics[0]);
        $this->assertEquals('on_time_delivery', $metrics[0]->getMetricType());
    }

    /**
     * Test getting top suppliers
     */
    public function testGetTopSuppliers(): void
    {
        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'supplier_id' => 123,
                    'current_score' => 92.5,
                    'rating' => 'excellent'
                ],
                [
                    'id' => 2,
                    'supplier_id' => 124,
                    'current_score' => 88.0,
                    'rating' => 'good'
                ]
            ]);

        $topSuppliers = $this->service->getTopSuppliers(10);

        $this->assertCount(2, $topSuppliers);
        $this->assertInstanceOf(SupplierRating::class, $topSuppliers[0]);
        $this->assertGreaterThanOrEqual($topSuppliers[1]->getCurrentScore(), $topSuppliers[0]->getCurrentScore());
    }

    /**
     * Test performance summary
     */
    public function testGetPerformanceSummary(): void
    {
        $supplierId = 123;

        $this->mockDBAL->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'avg_overall' => 87.5,
                    'avg_quality' => 85.0,
                    'avg_delivery' => 90.0,
                    'evaluation_count' => 4
                ],
                [
                    'id' => 1,
                    'supplier_id' => 123,
                    'current_score' => 87.5,
                    'rating' => 'good'
                ]
            );

        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                ['metric_type' => 'on_time_delivery', 'avg_value' => 95.5]
            ]);

        $summary = $this->service->getPerformanceSummary($supplierId, '2025-01-01', '2025-12-31');

        $this->assertEquals($supplierId, $summary['supplier_id']);
        $this->assertArrayHasKey('scores', $summary);
        $this->assertArrayHasKey('metrics', $summary);
        $this->assertEquals(87.5, $summary['scores']['avg_overall']);
    }

    /**
     * Test validation errors
     */
    public function testCreateEvaluationWithInvalidData(): void
    {
        $this->expectException(SupplierPerformanceValidationException::class);

        $invalidData = [
            'supplier_id' => 123,
            // Missing required fields
        ];

        $this->service->createEvaluation($invalidData);
    }

    /**
     * Test evaluation not found
     */
    public function testFinalizeNonExistentEvaluation(): void
    {
        $this->expectException(SupplierEvaluationNotFoundException::class);

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->service->finalizeEvaluation(999);
    }

    /**
     * Test rating determination
     */
    public function testRatingDetermination(): void
    {
        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->willReturn(1);

        $rating = $this->service->updateSupplierRating(123, 92.0);
        $this->assertEquals('excellent', $rating->getRating());

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->willReturn(2);

        $rating2 = $this->service->updateSupplierRating(124, 55.0);
        $this->assertEquals('poor', $rating2->getRating());
    }
}
