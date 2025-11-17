<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\TransactionIsClosedQuery;

/**
 * Validator for transaction closed status
 */
class TransactionNotClosedValidator
{
    private TransactionIsClosedQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        TransactionIsClosedQuery $query,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->query = $query;
        $this->errorHandler = $errorHandler;
    }

    public function validate(?string $msg = null): void
    {
        if ($this->query->getTypeNo() > 0 && $this->query->isClosed()) {
            if (!$msg) {
                global $systypes_array;
                $msg = sprintf(
                    \_("%s #%s is closed for further edition."),
                    $systypes_array[$this->query->getType()],
                    $this->query->getTypeNo()
                );
            }
            $this->errorHandler->handleValidationError($msg);
        }
    }
}
