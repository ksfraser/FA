<?php
/**
 * Display Service Interface
 *
 * Abstraction for display/output operations
 * Separates business logic from presentation (MVC pattern)
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface DisplayServiceInterface
{
    /**
     * Display an error message
     *
     * @param string $message Error message
     * @param bool $exit Whether to exit after displaying
     * @return void
     */
    public function displayError(string $message, bool $exit = false): void;
}
