<?php

namespace FA\Services;

use FA\Interfaces\DisplayServiceInterface;

/**
 * Display Service Implementation
 *
 * Concrete implementation of DisplayServiceInterface using FrontAccounting's global functions.
 *
 * @package FA\Services
 */
class DisplayService implements DisplayServiceInterface
{
    /**
     * Display an error message
     *
     * @param string $message Error message
     * @param bool $center Whether to center the message
     */
    public function displayError(string $message, bool $center = false): void
    {
        \UiMessageService::displayError($message, $center);
    }

    /**
     * Display a note
     *
     * @param string $message Note message
     */
    public function displayNote(string $message): void
    {
        \display_note($message);
    }

    /**
     * End the current page
     */
    public function endPage(): void
    {
        \end_page();
    }

    /**
     * Display footer and exit
     */
    public function displayFooterExit(): void
    {
        \display_footer_exit();
    }

    /**
     * Create a menu link
     *
     * @param string $url URL
     * @param string $text Link text
     * @return string HTML link
     */
    public function menuLink(string $url, string $text): string
    {
        return \menu_link($url, $text);
    }
}