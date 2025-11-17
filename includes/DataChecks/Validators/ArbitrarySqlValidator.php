<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\ArbitrarySqlQuery;

/**
 * Validator for arbitrary SQL query results
 */
class ArbitrarySqlValidator
{
    private ArbitrarySqlQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        ArbitrarySqlQuery $query,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->query = $query;
        $this->errorHandler = $errorHandler;
    }

    public function validate(string $errorMessage): void
    {
        if (!$this->query->hasResults()) {
            $this->errorHandler->handleValidationError($errorMessage);
        }
    }
}
