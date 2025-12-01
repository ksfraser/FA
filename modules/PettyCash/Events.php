<?php
/**
 * FrontAccounting PettyCash Module Events
 *
 * Event classes for Petty Cash functionality.
 *
 * @package FA\Modules\PettyCash
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\PettyCash\Events;

use FA\Modules\PettyCash\Entities\PettyCashTransaction;
use FA\Modules\PettyCash\Entities\PettyCashReimbursement;
use FA\Modules\PettyCash\Entities\PettyCashReceipt;

/**
 * Base Petty Cash Event
 *
 * Base class for all petty cash-related events.
 */
abstract class PettyCashEvent
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function getEntity()
    {
        return $this->entity;
    }
}

/**
 * Petty Cash Transaction Created Event
 *
 * Fired when a new petty cash transaction is created.
 */
class PettyCashTransactionCreatedEvent extends PettyCashEvent
{
    public function __construct(PettyCashTransaction $transaction)
    {
        parent::__construct($transaction);
    }

    public function getTransaction(): PettyCashTransaction
    {
        return $this->entity;
    }
}

/**
 * Petty Cash Transaction Approved Event
 *
 * Fired when a petty cash transaction is approved.
 */
class PettyCashTransactionApprovedEvent extends PettyCashEvent
{
    private int $approverId;

    public function __construct(PettyCashTransaction $transaction, int $approverId)
    {
        parent::__construct($transaction);
        $this->approverId = $approverId;
    }

    public function getTransaction(): PettyCashTransaction
    {
        return $this->entity;
    }

    public function getApproverId(): int
    {
        return $this->approverId;
    }
}

/**
 * Petty Cash Transaction Rejected Event
 *
 * Fired when a petty cash transaction is rejected.
 */
class PettyCashTransactionRejectedEvent extends PettyCashEvent
{
    private int $approverId;
    private string $reason;

    public function __construct(PettyCashTransaction $transaction, int $approverId, string $reason)
    {
        parent::__construct($transaction);
        $this->approverId = $approverId;
        $this->reason = $reason;
    }

    public function getTransaction(): PettyCashTransaction
    {
        return $this->entity;
    }

    public function getApproverId(): int
    {
        return $this->approverId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}

/**
 * Petty Cash Receipt Attached Event
 *
 * Fired when a receipt is attached to a transaction.
 */
class PettyCashReceiptAttachedEvent extends PettyCashEvent
{
    public function __construct(PettyCashReceipt $receipt)
    {
        parent::__construct($receipt);
    }

    public function getReceipt(): PettyCashReceipt
    {
        return $this->entity;
    }

    public function getTransactionId(): int
    {
        return $this->entity->getTransactionId();
    }
}

/**
 * Petty Cash Reimbursement Created Event
 *
 * Fired when a new reimbursement request is created.
 */
class PettyCashReimbursementCreatedEvent extends PettyCashEvent
{
    public function __construct(PettyCashReimbursement $reimbursement)
    {
        parent::__construct($reimbursement);
    }

    public function getReimbursement(): PettyCashReimbursement
    {
        return $this->entity;
    }
}

/**
 * Petty Cash Reimbursement Approved Event
 *
 * Fired when a reimbursement request is approved.
 */
class PettyCashReimbursementApprovedEvent extends PettyCashEvent
{
    private int $approverId;

    public function __construct(PettyCashReimbursement $reimbursement, int $approverId)
    {
        parent::__construct($reimbursement);
        $this->approverId = $approverId;
    }

    public function getReimbursement(): PettyCashReimbursement
    {
        return $this->entity;
    }

    public function getApproverId(): int
    {
        return $this->approverId;
    }
}

/**
 * Petty Cash Reimbursement Paid Event
 *
 * Fired when a reimbursement is paid.
 */
class PettyCashReimbursementPaidEvent extends PettyCashEvent
{
    private array $paymentData;

    public function __construct(PettyCashReimbursement $reimbursement, array $paymentData)
    {
        parent::__construct($reimbursement);
        $this->paymentData = $paymentData;
    }

    public function getReimbursement(): PettyCashReimbursement
    {
        return $this->entity;
    }

    public function getPaymentData(): array
    {
        return $this->paymentData;
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentData['payment_method'];
    }

    public function getProcessedBy(): int
    {
        return $this->paymentData['processed_by'];
    }
}