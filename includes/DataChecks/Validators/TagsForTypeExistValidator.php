<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\DataChecks\Queries\HasTagsForTypeQuery;

/**
 * Validator for tags existence
 */
class TagsForTypeExistValidator
{
    private HasTagsForTypeQuery $query;
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        HasTagsForTypeQuery $query,
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
