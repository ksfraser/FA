<?php
/**
 * Abstract Database Existence Validator
 *
 * Base class for all validators that check database existence
 * Single Responsibility: Validate and handle errors
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\ValidationErrorHandlerInterface;

abstract class AbstractDatabaseExistenceValidator
{
    protected AbstractDatabaseExistenceQuery $query;
    protected ValidationErrorHandlerInterface $errorHandler;

    public function __construct(
        AbstractDatabaseExistenceQuery $query,
        ValidationErrorHandlerInterface $errorHandler
    ) {
        $this->query = $query;
        $this->errorHandler = $errorHandler;
    }

    /**
     * Validate that entity exists, handle error if not
     *
     * @param string $errorMessage Error message to display
     * @return void
     */
    public function validate(string $errorMessage): void
    {
        if (!$this->query->exists()) {
            $this->errorHandler->handleValidationError($errorMessage, true);
        }
    }
}
