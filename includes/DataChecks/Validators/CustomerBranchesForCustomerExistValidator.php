<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\HasCustomerBranchesForCustomerQuery;

/**
 * Validator for customer having branches
 */
class CustomerBranchesForCustomerExistValidator
{
    private HasCustomerBranchesForCustomerQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        HasCustomerBranchesForCustomerQuery $query,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->query = $query;
        $this->errorHandler = $errorHandler;
    }

    public function validate(string $errorMessage): void
    {
        if (!$this->query->exists()) {
            $this->errorHandler->handleValidationError($errorMessage);
        }
    }
}
