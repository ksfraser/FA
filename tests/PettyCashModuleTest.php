<?php
/**
 * FrontAccounting PettyCash Module Tests
 *
 * Unit tests for PettyCash functionality.
 *
 * @package FA\Modules\PettyCash
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\PettyCash;

use PHPUnit\Framework\TestCase;
use FA\Modules\PettyCash\PettyCashService;
use FA\Modules\PettyCash\Entities\PettyCashTransaction;
use FA\Modules\PettyCash\Entities\PettyCashReimbursement;
use FA\Modules\PettyCash\Events\PettyCashTransactionCreatedEvent;
use FA\Modules\PettyCash\Events\PettyCashReimbursementApprovedEvent;
use FA\Modules\PettyCash\PettyCashException;

/**
 * PettyCash Module Test Suite
 */
class PettyCashModuleTest extends TestCase
{
    private PettyCashService $pettyCashService;
    private $mockDBAL;
    private $mockEventDispatcher;
    private $mockLogger;

    protected function setUp(): void
    {
        $this->mockDBAL = $this->createMock(\FA\Database\DBALInterface::class);
        $this->mockEventDispatcher = $this->createMock(\Psr\EventDispatcher\EventDispatcherInterface::class);
        $this->mockLogger = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->pettyCashService = new PettyCashService(
            $this->mockDBAL,
            $this->mockEventDispatcher,
            $this->mockLogger
        );
    }

    /**
     * Test creating a petty cash transaction
     */
    public function testCreatePettyCashTransaction(): void
    {
        $transactionData = [
            'employee_id' => 1,
            'amount' => 50.00,
            'description' => 'Office supplies',
            'category' => 'supplies',
            'receipt_required' => true,
            'transaction_date' => '2025-11-30'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('petty_cash_transactions', $this->callback(function($data) {
                return isset($data['transaction_reference']) &&
                       isset($data['employee_id']) &&
                       $data['amount'] == 50.00;
            }))
            ->willReturn(1);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PettyCashTransactionCreatedEvent::class));

        $transaction = $this->pettyCashService->createTransaction($transactionData);

        $this->assertInstanceOf(PettyCashTransaction::class, $transaction);
        $this->assertEquals(1, $transaction->getId());
        $this->assertEquals(50.00, $transaction->getAmount());
        $this->assertEquals('pending', $transaction->getStatus());
    }

    /**
     * Test approving a petty cash transaction
     */
    public function testApprovePettyCashTransaction(): void
    {
        $transactionId = 1;
        $approverId = 2;

        $this->mockDBAL->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1,
                    'transaction_reference' => 'PCT-2025-001',
                    'employee_id' => 1,
                    'amount' => 50.00,
                    'description' => 'Office supplies',
                    'category' => 'supplies',
                    'status' => 'pending',
                    'receipt_required' => true,
                    'receipt_attached' => false,
                    'approved_by' => null,
                    'approved_at' => null,
                    'transaction_date' => '2025-11-30',
                    'created_at' => '2025-11-30 10:00:00',
                    'updated_at' => '2025-11-30 10:00:00'
                ],
                [
                    'id' => 1,
                    'transaction_reference' => 'PCT-2025-001',
                    'employee_id' => 1,
                    'amount' => 50.00,
                    'description' => 'Office supplies',
                    'category' => 'supplies',
                    'status' => 'approved',
                    'receipt_required' => true,
                    'receipt_attached' => false,
                    'approved_by' => $approverId,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'transaction_date' => '2025-11-30',
                    'created_at' => '2025-11-30 10:00:00',
                    'updated_at' => '2025-11-30 10:00:00'
                ]
            );

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('petty_cash_transactions', $this->callback(function($data) {
                return $data['status'] === 'approved' &&
                       isset($data['approved_at']) &&
                       isset($data['approved_by']);
            }), ['id' => 1]);

        $transaction = $this->pettyCashService->approveTransaction($transactionId, $approverId);

        $this->assertEquals('approved', $transaction->getStatus());
        $this->assertEquals($approverId, $transaction->getApprovedBy());
    }    /**
     * Test rejecting a petty cash transaction
     */
    public function testRejectPettyCashTransaction(): void
    {
        $transactionId = 1;
        $approverId = 2;
        $reason = 'Insufficient documentation';

        $this->mockDBAL->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1,
                    'transaction_reference' => 'PCT-2025-001',
                    'employee_id' => 1,
                    'amount' => 50.00,
                    'status' => 'pending',
                    'created_at' => '2025-11-30 10:00:00'
                ],
                [
                    'id' => 1,
                    'transaction_reference' => 'PCT-2025-001',
                    'employee_id' => 1,
                    'amount' => 50.00,
                    'status' => 'rejected',
                    'rejection_reason' => $reason,
                    'created_at' => '2025-11-30 10:00:00'
                ]
            );

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('petty_cash_transactions', $this->callback(function($data) use ($reason) {
                return $data['status'] === 'rejected' &&
                       $data['rejection_reason'] === $reason;
            }), ['id' => 1]);

        $transaction = $this->pettyCashService->rejectTransaction($transactionId, $approverId, $reason);

        $this->assertEquals('rejected', $transaction->getStatus());
    }

    /**
     * Test attaching receipt to transaction
     */
    public function testAttachReceiptToTransaction(): void
    {
        $transactionId = 1;
        $receiptData = [
            'file_path' => '/uploads/receipts/receipt_001.pdf',
            'file_name' => 'receipt_001.pdf',
            'file_size' => 102400,
            'mime_type' => 'application/pdf'
        ];

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['id' => 1, 'receipt_required' => true, 'receipt_attached' => false]);

        $this->mockDBAL->expects($this->once())
            ->method('insert')
            ->with('petty_cash_receipts', $this->callback(function($data) use ($receiptData) {
                return $data['transaction_id'] == 1 &&
                       $data['file_path'] === $receiptData['file_path'];
            }))
            ->willReturn(1);

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('petty_cash_transactions', ['receipt_attached' => true], ['id' => 1]);

        $receipt = $this->pettyCashService->attachReceipt($transactionId, $receiptData);

        $this->assertEquals(1, $receipt->getId());
        $this->assertEquals($transactionId, $receipt->getTransactionId());
    }

    /**
     * Test creating a reimbursement request
     */
    public function testCreateReimbursementRequest(): void
    {
        $reimbursementData = [
            'employee_id' => 1,
            'amount' => 75.50,
            'description' => 'Business lunch with client',
            'category' => 'meals',
            'expense_date' => '2025-11-25',
            'receipts' => [
                ['file_path' => '/receipt1.pdf', 'amount' => 45.00],
                ['file_path' => '/receipt2.pdf', 'amount' => 30.50]
            ]
        ];

        $this->mockDBAL->expects($this->exactly(3))
            ->method('insert')
            ->willReturnOnConsecutiveCalls(1, 1, 2);

        $reimbursement = $this->pettyCashService->createReimbursement($reimbursementData);

        $this->assertInstanceOf(PettyCashReimbursement::class, $reimbursement);
        $this->assertEquals(75.50, $reimbursement->getAmount());
        $this->assertEquals('pending', $reimbursement->getStatus());
    }

    /**
     * Test approving reimbursement
     */
    public function testApproveReimbursement(): void
    {
        $reimbursementId = 1;
        $approverId = 2;

        $this->mockDBAL->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1,
                    'reimbursement_reference' => 'PCR-2025-001',
                    'employee_id' => 1,
                    'amount' => 75.50,
                    'status' => 'pending',
                    'created_at' => '2025-11-30 10:00:00'
                ],
                [
                    'id' => 1,
                    'reimbursement_reference' => 'PCR-2025-001',
                    'employee_id' => 1,
                    'amount' => 75.50,
                    'status' => 'approved',
                    'approved_by' => $approverId,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'created_at' => '2025-11-30 10:00:00'
                ]
            );

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('petty_cash_reimbursements', $this->callback(function($data) {
                return $data['status'] === 'approved';
            }), ['id' => 1]);

        $this->mockEventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(PettyCashReimbursementApprovedEvent::class));

        $reimbursement = $this->pettyCashService->approveReimbursement($reimbursementId, $approverId);

        $this->assertEquals('approved', $reimbursement->getStatus());
    }

    /**
     * Test processing reimbursement payment
     */
    public function testProcessReimbursementPayment(): void
    {
        $reimbursementId = 1;
        $paymentData = [
            'payment_method' => 'bank_transfer',
            'payment_reference' => 'PAY-2025-001',
            'processed_by' => 3
        ];

        $this->mockDBAL->expects($this->exactly(2))
            ->method('fetchOne')
            ->willReturnOnConsecutiveCalls(
                [
                    'id' => 1,
                    'status' => 'approved',
                    'amount' => 75.50
                ],
                [
                    'id' => 1,
                    'status' => 'paid',
                    'amount' => 75.50,
                    'payment_method' => $paymentData['payment_method'],
                    'payment_reference' => $paymentData['payment_reference'],
                    'paid_at' => date('Y-m-d H:i:s')
                ]
            );

        $this->mockDBAL->expects($this->once())
            ->method('update')
            ->with('petty_cash_reimbursements', $this->callback(function($data) use ($paymentData) {
                return $data['status'] === 'paid' &&
                       $data['payment_method'] === $paymentData['payment_method'] &&
                       isset($data['paid_at']);
            }), ['id' => 1]);

        $reimbursement = $this->pettyCashService->processPayment($reimbursementId, $paymentData);

        $this->assertEquals('paid', $reimbursement->getStatus());
        $this->assertEquals($paymentData['payment_method'], $reimbursement->getPaymentMethod());
    }

    /**
     * Test getting petty cash balance
     */
    public function testGetPettyCashBalance(): void
    {
        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(['total_balance' => 500.00]);

        $balance = $this->pettyCashService->getPettyCashBalance();

        $this->assertEquals(500.00, $balance);
    }

    /**
     * Test getting transactions by employee
     */
    public function testGetTransactionsByEmployee(): void
    {
        $employeeId = 1;

        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'transaction_reference' => 'PCT-2025-001',
                    'amount' => 50.00,
                    'status' => 'approved',
                    'transaction_date' => '2025-11-30'
                ]
            ]);

        $transactions = $this->pettyCashService->getTransactionsByEmployee($employeeId);

        $this->assertCount(1, $transactions);
        $this->assertInstanceOf(PettyCashTransaction::class, $transactions[0]);
        $this->assertEquals(50.00, $transactions[0]->getAmount());
    }

    /**
     * Test getting pending reimbursements
     */
    public function testGetPendingReimbursements(): void
    {
        $this->mockDBAL->expects($this->once())
            ->method('fetchAll')
            ->willReturn([
                [
                    'id' => 1,
                    'reimbursement_reference' => 'PCR-2025-001',
                    'employee_id' => 1,
                    'amount' => 75.50,
                    'status' => 'pending',
                    'created_at' => '2025-11-30 10:00:00'
                ]
            ]);

        $reimbursements = $this->pettyCashService->getPendingReimbursements();

        $this->assertCount(1, $reimbursements);
        $this->assertInstanceOf(PettyCashReimbursement::class, $reimbursements[0]);
        $this->assertEquals('pending', $reimbursements[0]->getStatus());
    }

    /**
     * Test transaction validation failure
     */
    public function testCreateTransactionWithInvalidData(): void
    {
        $this->expectException(PettyCashValidationException::class);

        $invalidData = [
            'employee_id' => 1,
            'amount' => -50.00, // Invalid negative amount
            'description' => 'Invalid transaction'
        ];

        $this->pettyCashService->createTransaction($invalidData);
    }

    /**
     * Test transaction not found
     */
    public function testApproveNonExistentTransaction(): void
    {
        $this->expectException(PettyCashTransactionNotFoundException::class);

        $this->mockDBAL->expects($this->once())
            ->method('fetchOne')
            ->willReturn(null);

        $this->pettyCashService->approveTransaction(999, 1);
    }
}