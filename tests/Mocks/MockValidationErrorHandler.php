<?php
/**
 * Mock Validation Error Handler
 *
 * Test double for ValidationErrorHandlerInterface
 * Captures errors without displaying them
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\ValidationErrorHandlerInterface;

class MockValidationErrorHandler implements ValidationErrorHandlerInterface
{
    private array $errors = [];
    private bool $exitCalled = false;

    public function handleValidationError(string $message, bool $fatal = true): void
    {
        $this->errors[] = [
            'message' => $message,
            'fatal' => $fatal
        ];
        
        if ($fatal) {
            $this->exitCalled = true;
            // Don't actually exit in tests
        }
    }

    /**
     * Get all captured errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Check if exit was called
     *
     * @return bool
     */
    public function wasExitCalled(): bool
    {
        return $this->exitCalled;
    }

    /**
     * Get last error message
     *
     * @return string|null
     */
    public function getLastError(): ?string
    {
        return empty($this->errors) ? null : end($this->errors)['message'];
    }

    /**
     * Check if has any errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Clear all errors
     */
    public function clear(): void
    {
        $this->errors = [];
        $this->exitCalled = false;
    }
}
