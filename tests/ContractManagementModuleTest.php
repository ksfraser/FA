<?php
/**
 * FrontAccounting ContractManagement Module Tests
 *
 * Unit tests for ContractManagement functionality.
 *
 * @package FA\Modules\ContractManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\ContractManagement;

use PHPUnit\Framework\TestCase;
use FA\Modules\ContractManagement\ContractManagementService;
use FA\Modules\ContractManagement\Entities\Contract;
use FA\Modules\ContractManagement\Entities\ContractMilestone;
use FA\Modules\ContractManagement\Entities\ContractDocument;
use FA\Modules\ContractManagement\Events\ContractCreatedEvent;
use FA\Modules\ContractManagement\ContractManagementException;

/**
 * ContractManagement Module Test Suite
 */
class ContractManagementModuleTest extends TestCase
{
    private ContractManagementService $contractService;
    private $mockDBAL;
    private $mockEventDispatcher;
    private $mockLogger;

    protected function setUp(): void
    {
        $this->mockDBAL = $this->createMock(\FA\Database\DBALInterface::class);
        $this->mockEventDispatcher = $this->createMock(\Psr\EventDispatcher\EventDispatcherInterface::class);
        $this->mockLogger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->contractService = new ContractManagementService(
            $this->mockDBAL,
            $this->mockEventDispatcher,
            $this->mockLogger
        );
    }

    /**
     * Test contract creation
     */
    public function testCreateContract(): void
    {
        $contractData = [
            'contract_reference' => 'CONT-2025-001',
            'contract_type' => 'service',
            'description' => 'IT Support Services Contract',
            'customer_id' => 'CUST001',
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'contract_value' => 50000.00,
            'currency' => 'USD',
            'status' => 'draft'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('contracts', $this->anything())
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractCreatedEvent::class));

        $contract = $this->contractService->createContract($contractData);

        $this->assertInstanceOf(Contract::class, $contract);
        $this->assertEquals('CONT-2025-001', $contract->getContractReference());
        $this->assertEquals('service', $contract->getContractType());
        $this->assertEquals(50000.00, $contract->getContractValue());
    }

    /**
     * Test contract milestone creation
     */
    public function testCreateContractMilestone(): void
    {
        $milestoneData = [
            'contract_id' => 1,
            'milestone_name' => 'Phase 1 Completion',
            'description' => 'Complete initial setup and configuration',
            'due_date' => '2025-03-31',
            'milestone_value' => 15000.00,
            'status' => 'pending'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('contract_milestones', $this->anything())
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractMilestoneCreatedEvent::class));

        $milestone = $this->contractService->createContractMilestone($milestoneData);

        $this->assertInstanceOf(ContractMilestone::class, $milestone);
        $this->assertEquals('Phase 1 Completion', $milestone->getMilestoneName());
        $this->assertEquals(15000.00, $milestone->getMilestoneValue());
    }

    /**
     * Test contract status update
     */
    public function testUpdateContractStatus(): void
    {
        $contractId = 1;
        $newStatus = 'active';

        // Mock getting contract
        $this->mockDBAL->expects($this->any())
            ->method('fetchOne')
            ->willReturn([
                'id' => 1,
                'contract_reference' => 'CONT-2025-001',
                'status' => 'draft',
                'start_date' => '2025-01-01',
                'end_date' => '2025-12-31',
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ]);

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('contracts', ['status' => 'active'], ['id' => 1]);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractStatusChangedEvent::class));

        $contract = $this->contractService->updateContractStatus($contractId, $newStatus);

        $this->assertEquals('active', $contract->getStatus());
    }

    /**
     * Test milestone completion
     */
    public function testCompleteContractMilestone(): void
    {
        $milestoneId = 1;

        // Mock getting milestone
        $this->mockDBAL->expects($this->any())
            ->method('fetchOne')
            ->willReturn([
                'id' => 1,
                'contract_id' => 1,
                'milestone_name' => 'Phase 1 Completion',
                'status' => 'pending',
                'milestone_value' => 15000.00,
                'due_date' => '2025-03-31',
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ]);

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('contract_milestones', $this->callback(function($data) {
                return isset($data['status']) && $data['status'] === 'completed' &&
                       isset($data['completed_date']);
            }), ['id' => 1]);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractMilestoneCompletedEvent::class));

        $milestone = $this->contractService->completeContractMilestone($milestoneId);

        $this->assertEquals('completed', $milestone->getStatus());
        $this->assertNotNull($milestone->getCompletedDate());
    }

    /**
     * Test contract document attachment
     */
    public function testAttachContractDocument(): void
    {
        $documentData = [
            'contract_id' => 1,
            'document_name' => 'Service Agreement.pdf',
            'document_type' => 'contract',
            'file_path' => '/uploads/contracts/cont-2025-001.pdf',
            'file_size' => 1024000,
            'uploaded_by' => 'user1'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('contract_documents', $this->anything())
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractDocumentAttachedEvent::class));

        $document = $this->contractService->attachContractDocument($documentData);

        $this->assertInstanceOf(ContractDocument::class, $document);
        $this->assertEquals('Service Agreement.pdf', $document->getDocumentName());
        $this->assertEquals('contract', $document->getDocumentType());
    }

    /**
     * Test contract renewal
     */
    public function testRenewContract(): void
    {
        $contractId = 1;
        $renewalData = [
            'new_end_date' => '2026-12-31',
            'renewal_value' => 55000.00,
            'renewal_terms' => 'Extended support services for additional year'
        ];

        // Mock getting contract
        $this->mockDBAL->expects($this->any())
            ->method('fetchOne')
            ->willReturn([
                'id' => 1,
                'contract_reference' => 'CONT-2025-001',
                'end_date' => '2025-12-31',
                'contract_value' => 50000.00,
                'status' => 'active',
                'created_at' => '2025-01-01 00:00:00',
                'updated_at' => '2025-01-01 00:00:00'
            ]);

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('contracts', $this->callback(function($data) {
                return isset($data['end_date']) && $data['end_date'] === '2026-12-31' &&
                       isset($data['contract_value']) && $data['contract_value'] == 55000.00;
            }), ['id' => 1]);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ContractRenewedEvent::class));

        $contract = $this->contractService->renewContract($contractId, $renewalData);

        $this->assertEquals('2026-12-31', $contract->getEndDate());
        $this->assertEquals(55000.00, $contract->getContractValue());
    }

    /**
     * Test contract validation
     */
    public function testCreateContractWithInvalidData(): void
    {
        $this->expectException(ContractValidationException::class);

        $invalidData = [
            'contract_reference' => '', // Empty reference should fail
            'start_date' => '2025-01-01',
            'end_date' => '2024-12-31' // End before start should fail
        ];

        $this->contractService->createContract($invalidData);
    }

    /**
     * Test contract not found
     */
    public function testGetContractNotFound(): void
    {
        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->expectException(ContractNotFoundException::class);

        $this->contractService->getContract(999);
    }

    /**
     * Test contract reporting
     */
    public function testGetContractReport(): void
    {
        $contractId = 1;

        // Mock contract data
        $this->mockDBAL->expects($this->any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Contract
                [['id' => 1, 'contract_reference' => 'CONT-2025-001', 'status' => 'active']],
                // Milestones
                [['id' => 1, 'milestone_name' => 'Phase 1', 'status' => 'completed', 'milestone_value' => 15000.00]],
                // Documents
                [['id' => 1, 'document_name' => 'Contract.pdf', 'document_type' => 'contract']],
                // Revenue recognition
                [['period' => '2025-01', 'recognized_revenue' => 4166.67]]
            );

        $report = $this->contractService->getContractReport($contractId);

        $this->assertIsArray($report);
        $this->assertArrayHasKey('contract', $report);
        $this->assertArrayHasKey('milestones', $report);
        $this->assertArrayHasKey('documents', $report);
        $this->assertArrayHasKey('revenue_recognition', $report);
        $this->assertArrayHasKey('summary', $report);
    }

    /**
     * Test contract expiry alerts
     */
    public function testGetExpiringContracts(): void
    {
        $daysAhead = 30;

        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'contract_reference' => 'CONT-2025-001',
                    'end_date' => date('Y-m-d', strtotime('+15 days')),
                    'customer_name' => 'ABC Corp',
                    'days_until_expiry' => 15
                ]
            ]);

        $expiringContracts = $this->contractService->getExpiringContracts($daysAhead);

        $this->assertIsArray($expiringContracts);
        $this->assertCount(1, $expiringContracts);
        $this->assertEquals(15, $expiringContracts[0]['days_until_expiry']);
    }

    /**
     * Test contract value analysis
     */
    public function testGetContractValueAnalysis(): void
    {
        $this->mockDBAL->expects($this->any())
            ->method('fetchAll')
            ->willReturnOnConsecutiveCalls(
                // Active contracts
                [['contract_value' => 50000.00], ['contract_value' => 75000.00]],
                // Monthly revenue
                [['month' => '2025-01', 'revenue' => 10416.67], ['month' => '2025-02', 'revenue' => 10416.67]]
            );

        $analysis = $this->contractService->getContractValueAnalysis();

        $this->assertIsArray($analysis);
        $this->assertArrayHasKey('total_contract_value', $analysis);
        $this->assertArrayHasKey('monthly_recurring_revenue', $analysis);
        $this->assertArrayHasKey('annual_recurring_revenue', $analysis);
        $this->assertEquals(125000.00, $analysis['total_contract_value']);
    }
}