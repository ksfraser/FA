<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\HasTemplateOrdersQuery;

/**
 * Validator for template orders existence
 */
class TemplateOrdersExistValidator
{
    private HasTemplateOrdersQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        HasTemplateOrdersQuery $query,
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
