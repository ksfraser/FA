<?php
/**
 * Validation Error Handler Interface
 *
 * Handles validation errors - separates business logic from presentation
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface ValidationErrorHandlerInterface
{
    /**
     * Handle a validation failure
     *
     * @param string $message Error message
     * @param bool $fatal Whether error is fatal (should exit)
     * @return void
     */
    public function handleValidationError(string $message, bool $fatal = true): void;
}
