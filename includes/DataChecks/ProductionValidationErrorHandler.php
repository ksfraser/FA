<?php
/**
 * Production Validation Error Handler
 *
 * Handles validation errors in production by displaying to user and exiting
 * Single Responsibility: Handle validation errors in web UI
 *
 * @package FA\DataChecks
 */

namespace FA\DataChecks;

use FA\Contracts\ValidationErrorHandlerInterface;

class ProductionValidationErrorHandler implements ValidationErrorHandlerInterface
{
    /**
     * Handle validation error by displaying and optionally exiting
     *
     * @param string $message Error message to display
     * @param bool $fatal Whether to exit after displaying
     * @return void
     */
    public function handleValidationError(string $message, bool $fatal = true): void
    {
        \UiMessageService::displayError($message, true);
        \end_page();
        
        if ($fatal) {
            exit;
        }
    }
}
