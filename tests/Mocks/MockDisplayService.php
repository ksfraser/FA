<?php
/**
 * Mock Display Service
 *
 * Test double for display operations
 * Implements DisplayServiceInterface for testing
 * Captures messages instead of outputting them
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\DisplayServiceInterface;

class MockDisplayService implements DisplayServiceInterface
{
    private array $errors = [];
    
    public function displayError(string $message, bool $exit = false): void
    {
        $this->errors[] = [
            'message' => $message,
            'exit' => $exit
        ];
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function clearErrors(): void
    {
        $this->errors = [];
    }
    
    public function hasErrors(): bool
    {
        return count($this->errors) > 0;
    }
}
