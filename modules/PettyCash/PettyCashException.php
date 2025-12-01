<?php
/**
 * FrontAccounting PettyCash Module Exceptions
 *
 * Custom exception classes for Petty Cash functionality.
 *
 * @package FA\Modules\PettyCash
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\PettyCash;

/**
 * Base Petty Cash Exception
 *
 * Base exception class for all petty cash-related errors.
 */
class PettyCashException extends \Exception
{
    protected int $entityId;

    public function __construct(
        string $message = "",
        int $entityId = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->entityId = $entityId;
    }

    public function getEntityId(): int
    {
        return $this->entityId;
    }
}

/**
 * Petty Cash Transaction Not Found Exception
 *
 * Thrown when a petty cash transaction cannot be found.
 */
class PettyCashTransactionNotFoundException extends PettyCashException
{
    public function __construct(int $transactionId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Petty cash transaction with ID {$transactionId} not found", $transactionId, $code, $previous);
    }
}

/**
 * Petty Cash Reimbursement Not Found Exception
 *
 * Thrown when a petty cash reimbursement cannot be found.
 */
class PettyCashReimbursementNotFoundException extends PettyCashException
{
    public function __construct(int $reimbursementId, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct("Petty cash reimbursement with ID {$reimbursementId} not found", $reimbursementId, $code, $previous);
    }
}

/**
 * Petty Cash Validation Exception
 *
 * Thrown when petty cash data validation fails.
 */
class PettyCashValidationException extends PettyCashException
{
    protected array $validationErrors;

    public function __construct(
        string $message = "",
        int $entityId = 0,
        array $validationErrors = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $entityId, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function hasValidationErrors(): bool
    {
        return !empty($this->validationErrors);
    }

    public function getValidationError(string $field): ?string
    {
        return $this->validationErrors[$field] ?? null;
    }
}

/**
 * Petty Cash Business Rule Exception
 *
 * Thrown when a business rule violation occurs.
 */
class PettyCashBusinessRuleException extends PettyCashException
{
    protected string $ruleName;

    public function __construct(
        string $message,
        int $entityId = 0,
        string $ruleName = '',
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $entityId, $code, $previous);
        $this->ruleName = $ruleName;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
    }
}

/**
 * Petty Cash Insufficient Funds Exception
 *
 * Thrown when there are insufficient petty cash funds.
 */
class PettyCashInsufficientFundsException extends PettyCashException
{
    protected float $requestedAmount;
    protected float $availableBalance;

    public function __construct(
        float $requestedAmount,
        float $availableBalance,
        int $entityId = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = "Insufficient petty cash funds. Requested: \${$requestedAmount}, Available: \${$availableBalance}";
        parent::__construct($message, $entityId, $code, $previous);
        $this->requestedAmount = $requestedAmount;
        $this->availableBalance = $availableBalance;
    }

    public function getRequestedAmount(): float
    {
        return $this->requestedAmount;
    }

    public function getAvailableBalance(): float
    {
        return $this->availableBalance;
    }

    public function getShortfall(): float
    {
        return $this->requestedAmount - $this->availableBalance;
    }
}

/**
 * Petty Cash File Upload Exception
 *
 * Thrown when file upload for receipts fails.
 */
class PettyCashFileUploadException extends PettyCashException
{
    protected string $fileName;
    protected string $error;

    public function __construct(
        string $message,
        string $fileName,
        string $error = '',
        int $entityId = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $entityId, $code, $previous);
        $this->fileName = $fileName;
        $this->error = $error;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getError(): string
    {
        return $this->error;
    }
}

/**
 * Petty Cash Permission Exception
 *
 * Thrown when a user doesn't have permission to perform an action.
 */
class PettyCashPermissionException extends PettyCashException
{
    protected int $userId;
    protected string $requiredPermission;

    public function __construct(
        string $message,
        int $userId,
        string $requiredPermission,
        int $entityId = 0,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $entityId, $code, $previous);
        $this->userId = $userId;
        $this->requiredPermission = $requiredPermission;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRequiredPermission(): string
    {
        return $this->requiredPermission;
    }
}

/**
 * Petty Cash Status Transition Exception
 *
 * Thrown when an invalid status transition is attempted.
 */
class PettyCashStatusTransitionException extends PettyCashException
{
    protected string $currentStatus;
    protected string $newStatus;
    protected string $entityType;

    public function __construct(
        string $entityType,
        int $entityId,
        string $currentStatus,
        string $newStatus,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = "Invalid status transition for {$entityType} {$entityId} from '{$currentStatus}' to '{$newStatus}'";
        parent::__construct($message, $entityId, $code, $previous);
        $this->currentStatus = $currentStatus;
        $this->newStatus = $newStatus;
        $this->entityType = $entityType;
    }

    public function getCurrentStatus(): string
    {
        return $this->currentStatus;
    }

    public function getNewStatus(): string
    {
        return $this->newStatus;
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }
}