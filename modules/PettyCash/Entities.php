<?php
/**
 * FrontAccounting PettyCash Module Entities
 *
 * Entity classes for Petty Cash functionality.
 *
 * @package FA\Modules\PettyCash
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\PettyCash\Entities;

/**
 * Petty Cash Transaction Entity
 *
 * Represents a petty cash transaction in the system.
 */
class PettyCashTransaction
{
    private int $id;
    private string $transactionReference;
    private int $employeeId;
    private float $amount;
    private string $description;
    private string $category;
    private string $status;
    private bool $receiptRequired;
    private bool $receiptAttached;
    private ?int $approvedBy;
    private ?string $approvedAt;
    private ?string $rejectedAt;
    private ?string $rejectionReason;
    private string $transactionDate;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->transactionReference = $data['transaction_reference'] ?? '';
        $this->employeeId = $data['employee_id'] ?? 0;
        $this->amount = (float)($data['amount'] ?? 0.00);
        $this->description = $data['description'] ?? '';
        $this->category = $data['category'] ?? 'general';
        $this->status = $data['status'] ?? 'pending';
        $this->receiptRequired = (bool)($data['receipt_required'] ?? false);
        $this->receiptAttached = (bool)($data['receipt_attached'] ?? false);
        $this->approvedBy = $data['approved_by'] ?? null;
        $this->approvedAt = $data['approved_at'] ?? null;
        $this->rejectedAt = $data['rejected_at'] ?? null;
        $this->rejectionReason = $data['rejection_reason'] ?? null;
        $this->transactionDate = $data['transaction_date'] ?? '';
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTransactionReference(): string { return $this->transactionReference; }
    public function getEmployeeId(): int { return $this->employeeId; }
    public function getAmount(): float { return $this->amount; }
    public function getDescription(): string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getStatus(): string { return $this->status; }
    public function isReceiptRequired(): bool { return $this->receiptRequired; }
    public function isReceiptAttached(): bool { return $this->receiptAttached; }
    public function getApprovedBy(): ?int { return $this->approvedBy; }
    public function getApprovedAt(): ?string { return $this->approvedAt; }
    public function getRejectedAt(): ?string { return $this->rejectedAt; }
    public function getRejectionReason(): ?string { return $this->rejectionReason; }
    public function getTransactionDate(): string { return $this->transactionDate; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Business logic methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function requiresApproval(): bool
    {
        return $this->amount > 25.00 || $this->receiptRequired;
    }

    public function canBeApproved(): bool
    {
        return $this->isPending() && (!$this->receiptRequired || $this->receiptAttached);
    }

    public function getDaysSinceCreated(): int
    {
        $created = strtotime($this->createdAt);
        $now = time();
        return (int)(($now - $created) / (60 * 60 * 24));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'transaction_reference' => $this->transactionReference,
            'employee_id' => $this->employeeId,
            'amount' => $this->amount,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
            'receipt_required' => $this->receiptRequired,
            'receipt_attached' => $this->receiptAttached,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
            'rejected_at' => $this->rejectedAt,
            'rejection_reason' => $this->rejectionReason,
            'transaction_date' => $this->transactionDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}

/**
 * Petty Cash Reimbursement Entity
 *
 * Represents a petty cash reimbursement request.
 */
class PettyCashReimbursement
{
    private int $id;
    private string $reimbursementReference;
    private int $employeeId;
    private float $amount;
    private string $description;
    private string $category;
    private string $expenseDate;
    private string $status;
    private bool $receiptsAttached;
    private ?int $approvedBy;
    private ?string $approvedAt;
    private ?string $paidAt;
    private ?string $paymentMethod;
    private ?string $paymentReference;
    private ?int $processedBy;
    private string $createdAt;
    private string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->reimbursementReference = $data['reimbursement_reference'] ?? '';
        $this->employeeId = $data['employee_id'] ?? 0;
        $this->amount = (float)($data['amount'] ?? 0.00);
        $this->description = $data['description'] ?? '';
        $this->category = $data['category'] ?? 'general';
        $this->expenseDate = $data['expense_date'] ?? '';
        $this->status = $data['status'] ?? 'pending';
        $this->receiptsAttached = (bool)($data['receipts_attached'] ?? false);
        $this->approvedBy = $data['approved_by'] ?? null;
        $this->approvedAt = $data['approved_at'] ?? null;
        $this->paidAt = $data['paid_at'] ?? null;
        $this->paymentMethod = $data['payment_method'] ?? null;
        $this->paymentReference = $data['payment_reference'] ?? null;
        $this->processedBy = $data['processed_by'] ?? null;
        $this->createdAt = $data['created_at'] ?? '';
        $this->updatedAt = $data['updated_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getReimbursementReference(): string { return $this->reimbursementReference; }
    public function getEmployeeId(): int { return $this->employeeId; }
    public function getAmount(): float { return $this->amount; }
    public function getDescription(): string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getExpenseDate(): string { return $this->expenseDate; }
    public function getStatus(): string { return $this->status; }
    public function hasReceiptsAttached(): bool { return $this->receiptsAttached; }
    public function getApprovedBy(): ?int { return $this->approvedBy; }
    public function getApprovedAt(): ?string { return $this->approvedAt; }
    public function getPaidAt(): ?string { return $this->paidAt; }
    public function getPaymentMethod(): ?string { return $this->paymentMethod; }
    public function getPaymentReference(): ?string { return $this->paymentReference; }
    public function getProcessedBy(): ?int { return $this->processedBy; }
    public function getCreatedAt(): string { return $this->createdAt; }
    public function getUpdatedAt(): string { return $this->updatedAt; }

    // Business logic methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function requiresApproval(): bool
    {
        return $this->amount > 50.00 || !$this->receiptsAttached;
    }

    public function canBePaid(): bool
    {
        return $this->isApproved();
    }

    public function getDaysSinceExpense(): int
    {
        $expense = strtotime($this->expenseDate);
        $now = time();
        return (int)(($now - $expense) / (60 * 60 * 24));
    }

    public function getProcessingDays(): int
    {
        if (!$this->paidAt) {
            return 0;
        }

        $created = strtotime($this->createdAt);
        $paid = strtotime($this->paidAt);
        return (int)(($paid - $created) / (60 * 60 * 24));
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'reimbursement_reference' => $this->reimbursementReference,
            'employee_id' => $this->employeeId,
            'amount' => $this->amount,
            'description' => $this->description,
            'category' => $this->category,
            'expense_date' => $this->expenseDate,
            'status' => $this->status,
            'receipts_attached' => $this->receiptsAttached,
            'approved_by' => $this->approvedBy,
            'approved_at' => $this->approvedAt,
            'paid_at' => $this->paidAt,
            'payment_method' => $this->paymentMethod,
            'payment_reference' => $this->paymentReference,
            'processed_by' => $this->processedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }
}

/**
 * Petty Cash Receipt Entity
 *
 * Represents a receipt attached to a petty cash transaction.
 */
class PettyCashReceipt
{
    private int $id;
    private int $transactionId;
    private string $filePath;
    private string $fileName;
    private ?int $fileSize;
    private ?string $mimeType;
    private ?int $uploadedBy;
    private string $uploadedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? 0;
        $this->transactionId = $data['transaction_id'] ?? 0;
        $this->filePath = $data['file_path'] ?? '';
        $this->fileName = $data['file_name'] ?? '';
        $this->fileSize = $data['file_size'] ?? null;
        $this->mimeType = $data['mime_type'] ?? null;
        $this->uploadedBy = $data['uploaded_by'] ?? null;
        $this->uploadedAt = $data['uploaded_at'] ?? '';
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTransactionId(): int { return $this->transactionId; }
    public function getFilePath(): string { return $this->filePath; }
    public function getFileName(): string { return $this->fileName; }
    public function getFileSize(): ?int { return $this->fileSize; }
    public function getMimeType(): ?string { return $this->mimeType; }
    public function getUploadedBy(): ?int { return $this->uploadedBy; }
    public function getUploadedAt(): string { return $this->uploadedAt; }

    // Business logic methods
    public function isImage(): bool
    {
        $imageTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        return $this->mimeType && in_array($this->mimeType, $imageTypes);
    }

    public function isPdf(): bool
    {
        return $this->mimeType === 'application/pdf';
    }

    public function getFileExtension(): string
    {
        return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION));
    }

    public function getFormattedFileSize(): string
    {
        if ($this->fileSize === null) {
            return 'Unknown';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->fileSize;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'transaction_id' => $this->transactionId,
            'file_path' => $this->filePath,
            'file_name' => $this->fileName,
            'file_size' => $this->fileSize,
            'mime_type' => $this->mimeType,
            'uploaded_by' => $this->uploadedBy,
            'uploaded_at' => $this->uploadedAt
        ];
    }
}