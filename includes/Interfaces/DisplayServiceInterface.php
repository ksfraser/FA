<?php

namespace FA\Interfaces;

/**
 * Display Service Interface
 *
 * Abstracts UI display operations for dependency injection.
 * Enables testing and swapping display implementations.
 *
 * @package FA\Interfaces
 */
interface DisplayServiceInterface
{
    /**
     * Display an error message
     *
     * @param string $message Error message
     * @param bool $center Whether to center the message
     */
    public function displayError(string $message, bool $center = false): void;

    /**
     * Display a note
     *
     * @param string $message Note message
     */
    public function displayNote(string $message): void;

    /**
     * End the current page
     */
    public function endPage(): void;

    /**
     * Display footer and exit
     */
    public function displayFooterExit(): void;

    /**
     * Create a menu link
     *
     * @param string $url URL
     * @param string $text Link text
     * @return string HTML link
     */
    public function menuLink(string $url, string $text): string;
}