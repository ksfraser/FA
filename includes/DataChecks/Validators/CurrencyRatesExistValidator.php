<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\HasCurrencyRatesQuery;

/**
 * Validator for currency rates existence
 */
class CurrencyRatesExistValidator
{
    private HasCurrencyRatesQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        HasCurrencyRatesQuery $query,
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
