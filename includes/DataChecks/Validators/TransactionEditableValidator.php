<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\TransactionIsEditableQuery;
use FA\DataChecks\Queries\TransactionIsClosedQuery;

/**
 * Validator for transaction editability
 */
class TransactionEditableValidator
{
    private TransactionIsEditableQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        TransactionIsEditableQuery $query,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->query = $query;
        $this->errorHandler = $errorHandler;
    }

    public function validate(?string $msg = null): void
    {
        if (!$this->query->canEdit()) {
            if (!$msg) {
                $msg = '<b>' . \_("You have no edit access to transactions created by other users.") . '</b>';
            }
            // Uses display_note instead of display_error
            \display_note($msg);
            \display_footer_exit();
        }

        // Check if transaction is closed (unless it's an order type)
        $transType = $this->query->getTransType();
        if (!in_array($transType, array(\ST_SALESORDER, \ST_SALESQUOTE, \ST_PURCHORDER, \ST_WORKORDER))) {
            $closedQuery = new TransactionIsClosedQuery(
                new \FA\DataChecks\ProductionDatabaseQuery(),
                $transType,
                $this->query->getTransNo()
            );
            $closedValidator = new TransactionNotClosedValidator($closedQuery, $this->errorHandler);
            $closedValidator->validate($msg);
        }
    }
}
