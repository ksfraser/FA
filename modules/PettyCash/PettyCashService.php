<?php
/**
 * FrontAccounting PettyCash Module Service
 *
 * Main service class for Petty Cash functionality.
 *
 * @package FA\Modules\PettyCash
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\PettyCash;

use FA\Database\DBALInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use FA\Modules\PettyCash\Entities\PettyCashTransaction;
use FA\Modules\PettyCash\Entities\PettyCashReimbursement;
use FA\Modules\PettyCash\Entities\PettyCashReceipt;
use FA\Modules\PettyCash\Events\PettyCashTransactionCreatedEvent;
use FA\Modules\PettyCash\Events\PettyCashTransactionApprovedEvent;
use FA\Modules\PettyCash\Events\PettyCashTransactionRejectedEvent;
use FA\Modules\PettyCash\Events\PettyCashReimbursementCreatedEvent;
use FA\Modules\PettyCash\Events\PettyCashReimbursementApprovedEvent;
use FA\Modules\PettyCash\Events\PettyCashReimbursementPaidEvent;
use FA\Modules\PettyCash\Events\PettyCashReceiptAttachedEvent;

/**
 * Petty Cash Service
 *
 * Handles petty cash transactions, reimbursements, and receipt management.
 */
class PettyCashService
{
    private DBALInterface $dbal;
    private EventDispatcherInterface $eventDispatcher;
    private LoggerInterface $logger;

    public function __construct(
        DBALInterface $dbal,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger
    ) {
        $this->dbal = $dbal;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
    }

    /**
     * Create a new petty cash transaction
     *
     * @param array $data Transaction data
     * @return PettyCashTransaction
     * @throws PettyCashValidationException
     */
    public function createTransaction(array $data): PettyCashTransaction
    {
        $this->validateTransactionData($data);

        $transactionData = [
            'transaction_reference' => $this->generateTransactionReference(),
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? '',
            'category' => $data['category'] ?? 'general',
            'status' => 'pending',
            'receipt_required' => $data['receipt_required'] ?? false,
            'receipt_attached' => false,
            'transaction_date' => $data['transaction_date'] ?? date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $transactionId = $this->dbal->insert('petty_cash_transactions', $transactionData);

        $transaction = new PettyCashTransaction(array_merge($transactionData, ['id' => $transactionId]));

        $this->logger->info('Petty cash transaction created', [
            'transaction_id' => $transactionId,
            'reference' => $transaction->getTransactionReference(),
            'amount' => $transaction->getAmount(),
            'employee_id' => $transaction->getEmployeeId()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashTransactionCreatedEvent($transaction));

        return $transaction;
    }

    /**
     * Approve a petty cash transaction
     *
     * @param int $transactionId
     * @param int $approverId
     * @return PettyCashTransaction
     */
    public function approveTransaction(int $transactionId, int $approverId): PettyCashTransaction
    {
        $transaction = $this->getTransaction($transactionId);

        if ($transaction->getStatus() !== 'pending') {
            throw new PettyCashBusinessRuleException(
                "Cannot approve transaction with status '{$transaction->getStatus()}'",
                $transactionId
            );
        }

        $updateData = [
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->update('petty_cash_transactions', $updateData, ['id' => $transactionId]);

        $transaction = $this->getTransaction($transactionId);

        $this->logger->info('Petty cash transaction approved', [
            'transaction_id' => $transactionId,
            'approver_id' => $approverId,
            'amount' => $transaction->getAmount()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashTransactionApprovedEvent($transaction, $approverId));

        return $transaction;
    }

    /**
     * Reject a petty cash transaction
     *
     * @param int $transactionId
     * @param int $approverId
     * @param string $reason
     * @return PettyCashTransaction
     */
    public function rejectTransaction(int $transactionId, int $approverId, string $reason): PettyCashTransaction
    {
        $transaction = $this->getTransaction($transactionId);

        if ($transaction->getStatus() !== 'pending') {
            throw new PettyCashBusinessRuleException(
                "Cannot reject transaction with status '{$transaction->getStatus()}'",
                $transactionId
            );
        }

        $updateData = [
            'status' => 'rejected',
            'approved_by' => $approverId,
            'rejection_reason' => $reason,
            'rejected_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->update('petty_cash_transactions', $updateData, ['id' => $transactionId]);

        $transaction = $this->getTransaction($transactionId);

        $this->logger->info('Petty cash transaction rejected', [
            'transaction_id' => $transactionId,
            'approver_id' => $approverId,
            'reason' => $reason
        ]);

        $this->eventDispatcher->dispatch(new PettyCashTransactionRejectedEvent($transaction, $approverId, $reason));

        return $transaction;
    }

    /**
     * Attach receipt to transaction
     *
     * @param int $transactionId
     * @param array $receiptData
     * @return PettyCashReceipt
     */
    public function attachReceipt(int $transactionId, array $receiptData): PettyCashReceipt
    {
        $transaction = $this->getTransaction($transactionId);

        if (!$transaction->isReceiptRequired()) {
            throw new PettyCashBusinessRuleException(
                'Receipt not required for this transaction',
                $transactionId
            );
        }

        $receiptDataFull = [
            'transaction_id' => $transactionId,
            'file_path' => $receiptData['file_path'],
            'file_name' => $receiptData['file_name'],
            'file_size' => $receiptData['file_size'] ?? null,
            'mime_type' => $receiptData['mime_type'] ?? null,
            'uploaded_by' => $receiptData['uploaded_by'] ?? null,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];

        $receiptId = $this->dbal->insert('petty_cash_receipts', $receiptDataFull);

        // Mark transaction as having receipt attached
        $this->dbal->update('petty_cash_transactions', ['receipt_attached' => true], ['id' => $transactionId]);

        $receipt = new PettyCashReceipt(array_merge($receiptDataFull, ['id' => $receiptId]));

        $this->logger->info('Receipt attached to petty cash transaction', [
            'transaction_id' => $transactionId,
            'receipt_id' => $receiptId,
            'filename' => $receipt->getFileName()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashReceiptAttachedEvent($receipt));

        return $receipt;
    }

    /**
     * Create a reimbursement request
     *
     * @param array $data Reimbursement data
     * @return PettyCashReimbursement
     */
    public function createReimbursement(array $data): PettyCashReimbursement
    {
        $this->validateReimbursementData($data);

        $reimbursementData = [
            'reimbursement_reference' => $this->generateReimbursementReference(),
            'employee_id' => $data['employee_id'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? '',
            'category' => $data['category'] ?? 'general',
            'expense_date' => $data['expense_date'] ?? date('Y-m-d'),
            'status' => 'pending',
            'receipts_attached' => !empty($data['receipts']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $reimbursementId = $this->dbal->insert('petty_cash_reimbursements', $reimbursementData);

        $reimbursement = new PettyCashReimbursement(array_merge($reimbursementData, ['id' => $reimbursementId]));

        // Attach receipts if provided
        if (!empty($data['receipts'])) {
            foreach ($data['receipts'] as $receiptData) {
                $this->attachReimbursementReceipt($reimbursementId, $receiptData);
            }
        }

        $this->logger->info('Petty cash reimbursement created', [
            'reimbursement_id' => $reimbursementId,
            'reference' => $reimbursement->getReimbursementReference(),
            'amount' => $reimbursement->getAmount(),
            'employee_id' => $reimbursement->getEmployeeId()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashReimbursementCreatedEvent($reimbursement));

        return $reimbursement;
    }

    /**
     * Approve a reimbursement request
     *
     * @param int $reimbursementId
     * @param int $approverId
     * @return PettyCashReimbursement
     */
    public function approveReimbursement(int $reimbursementId, int $approverId): PettyCashReimbursement
    {
        $reimbursement = $this->getReimbursement($reimbursementId);

        if ($reimbursement->getStatus() !== 'pending') {
            throw new PettyCashBusinessRuleException(
                "Cannot approve reimbursement with status '{$reimbursement->getStatus()}'",
                $reimbursementId
            );
        }

        $updateData = [
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->update('petty_cash_reimbursements', $updateData, ['id' => $reimbursementId]);

        $reimbursement = $this->getReimbursement($reimbursementId);

        $this->logger->info('Petty cash reimbursement approved', [
            'reimbursement_id' => $reimbursementId,
            'approver_id' => $approverId,
            'amount' => $reimbursement->getAmount()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashReimbursementApprovedEvent($reimbursement, $approverId));

        return $reimbursement;
    }

    /**
     * Process payment for approved reimbursement
     *
     * @param int $reimbursementId
     * @param array $paymentData
     * @return PettyCashReimbursement
     */
    public function processPayment(int $reimbursementId, array $paymentData): PettyCashReimbursement
    {
        $reimbursement = $this->getReimbursement($reimbursementId);

        if ($reimbursement->getStatus() !== 'approved') {
            throw new PettyCashBusinessRuleException(
                "Cannot process payment for reimbursement with status '{$reimbursement->getStatus()}'",
                $reimbursementId
            );
        }

        $updateData = [
            'status' => 'paid',
            'payment_method' => $paymentData['payment_method'],
            'payment_reference' => $paymentData['payment_reference'] ?? null,
            'processed_by' => $paymentData['processed_by'],
            'paid_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->update('petty_cash_reimbursements', $updateData, ['id' => $reimbursementId]);

        $reimbursement = $this->getReimbursement($reimbursementId);

        $this->logger->info('Petty cash reimbursement payment processed', [
            'reimbursement_id' => $reimbursementId,
            'payment_method' => $paymentData['payment_method'],
            'amount' => $reimbursement->getAmount()
        ]);

        $this->eventDispatcher->dispatch(new PettyCashReimbursementPaidEvent($reimbursement, $paymentData));

        return $reimbursement;
    }

    /**
     * Get transaction by ID
     *
     * @param int $transactionId
     * @return PettyCashTransaction
     */
    public function getTransaction(int $transactionId): PettyCashTransaction
    {
        $sql = "SELECT * FROM petty_cash_transactions WHERE id = ?";
        $data = $this->dbal->fetchOne($sql, [$transactionId]);

        if (!$data) {
            throw new PettyCashTransactionNotFoundException($transactionId);
        }

        return new PettyCashTransaction($data);
    }

    /**
     * Get reimbursement by ID
     *
     * @param int $reimbursementId
     * @return PettyCashReimbursement
     */
    public function getReimbursement(int $reimbursementId): PettyCashReimbursement
    {
        $sql = "SELECT * FROM petty_cash_reimbursements WHERE id = ?";
        $data = $this->dbal->fetchOne($sql, [$reimbursementId]);

        if (!$data) {
            throw new PettyCashReimbursementNotFoundException($reimbursementId);
        }

        return new PettyCashReimbursement($data);
    }

    /**
     * Get petty cash balance
     *
     * @return float
     */
    public function getPettyCashBalance(): float
    {
        $sql = "
            SELECT COALESCE(SUM(amount), 0) as total_balance
            FROM petty_cash_transactions
            WHERE status = 'approved'
        ";
        $result = $this->dbal->fetchOne($sql);
        return (float)($result['total_balance'] ?? 0);
    }

    /**
     * Get transactions by employee
     *
     * @param int $employeeId
     * @param int $limit
     * @return PettyCashTransaction[]
     */
    public function getTransactionsByEmployee(int $employeeId, int $limit = 50): array
    {
        $sql = "
            SELECT * FROM petty_cash_transactions
            WHERE employee_id = ?
            ORDER BY created_at DESC
            LIMIT ?
        ";
        $transactionsData = $this->dbal->fetchAll($sql, [$employeeId, $limit]);

        $transactions = [];
        foreach ($transactionsData as $data) {
            $transactions[] = new PettyCashTransaction($data);
        }

        return $transactions;
    }

    /**
     * Get pending reimbursements
     *
     * @param int $limit
     * @return PettyCashReimbursement[]
     */
    public function getPendingReimbursements(int $limit = 100): array
    {
        $sql = "
            SELECT * FROM petty_cash_reimbursements
            WHERE status = 'pending'
            ORDER BY created_at ASC
            LIMIT ?
        ";
        $reimbursementsData = $this->dbal->fetchAll($sql, [$limit]);

        $reimbursements = [];
        foreach ($reimbursementsData as $data) {
            $reimbursements[] = new PettyCashReimbursement($data);
        }

        return $reimbursements;
    }

    /**
     * Get monthly petty cash summary
     *
     * @param string $month Year-month in YYYY-MM format
     * @return array
     */
    public function getMonthlySummary(string $month): array
    {
        $sql = "
            SELECT
                COUNT(*) as total_transactions,
                SUM(CASE WHEN status = 'approved' THEN amount ELSE 0 END) as approved_amount,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
            FROM petty_cash_transactions
            WHERE DATE_FORMAT(transaction_date, '%Y-%m') = ?
        ";
        $transactionSummary = $this->dbal->fetchOne($sql, [$month]);

        $sql = "
            SELECT
                COUNT(*) as total_reimbursements,
                SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reimbursements
            FROM petty_cash_reimbursements
            WHERE DATE_FORMAT(created_at, '%Y-%m') = ?
        ";
        $reimbursementSummary = $this->dbal->fetchOne($sql, [$month]);

        return [
            'month' => $month,
            'transactions' => $transactionSummary,
            'reimbursements' => $reimbursementSummary,
            'total_expenses' => ($transactionSummary['approved_amount'] ?? 0) + ($reimbursementSummary['paid_amount'] ?? 0)
        ];
    }

    // Private helper methods

    private function validateTransactionData(array $data): void
    {
        $errors = [];

        if (empty($data['employee_id']) || !is_numeric($data['employee_id'])) {
            $errors['employee_id'] = 'Valid employee ID is required';
        }

        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Valid positive amount is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }

        $validCategories = ['supplies', 'meals', 'travel', 'utilities', 'maintenance', 'general'];
        if (isset($data['category']) && !in_array($data['category'], $validCategories)) {
            $errors['category'] = 'Invalid category';
        }

        if (!empty($errors)) {
            throw new PettyCashValidationException('Transaction validation failed', 0, $errors);
        }
    }

    private function validateReimbursementData(array $data): void
    {
        $errors = [];

        if (empty($data['employee_id']) || !is_numeric($data['employee_id'])) {
            $errors['employee_id'] = 'Valid employee ID is required';
        }

        if (!isset($data['amount']) || !is_numeric($data['amount']) || $data['amount'] <= 0) {
            $errors['amount'] = 'Valid positive amount is required';
        }

        if (empty($data['description'])) {
            $errors['description'] = 'Description is required';
        }

        if (!empty($errors)) {
            throw new PettyCashValidationException('Reimbursement validation failed', 0, $errors);
        }
    }

    private function generateTransactionReference(): string
    {
        $date = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM petty_cash_transactions WHERE DATE(created_at) = CURDATE()";
        $result = $this->dbal->fetchOne($sql);
        $sequence = (int)($result['count'] ?? 0) + 1;
        return 'PCT-' . date('Y') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    private function generateReimbursementReference(): string
    {
        $date = date('Y-m-d');
        $sql = "SELECT COUNT(*) as count FROM petty_cash_reimbursements WHERE DATE(created_at) = CURDATE()";
        $result = $this->dbal->fetchOne($sql);
        $sequence = (int)($result['count'] ?? 0) + 1;
        return 'PCR-' . date('Y') . '-' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    private function attachReimbursementReceipt(int $reimbursementId, array $receiptData): void
    {
        $receiptDataFull = [
            'reimbursement_id' => $reimbursementId,
            'file_path' => $receiptData['file_path'],
            'file_name' => $receiptData['file_name'] ?? basename($receiptData['file_path']),
            'file_size' => $receiptData['file_size'] ?? null,
            'mime_type' => $receiptData['mime_type'] ?? null,
            'amount' => $receiptData['amount'] ?? null,
            'uploaded_at' => date('Y-m-d H:i:s')
        ];

        $this->dbal->insert('petty_cash_reimbursement_receipts', $receiptDataFull);
    }
}