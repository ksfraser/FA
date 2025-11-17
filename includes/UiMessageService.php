<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * UI Message Service
 * 
 * Handles user interface messages, errors, warnings, and notifications.
 * Replaces legacy UiMessageService::displayError(), display_notification(), display_warning() functions.
 */
class UiMessageService
{
    /**
     * Display error message
     * 
     * Triggers an error that will be shown to the user.
     * 
     * @param string $msg The error message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayError(string $msg, bool $center = true): void
    {
        fa_trigger_error($msg, E_USER_ERROR);
    }
    
    /**
     * Display notification message
     * 
     * Triggers a notice that will be shown to the user.
     * 
     * @param string $msg The notification message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayNotification(string $msg, bool $center = true): void
    {
        fa_trigger_error($msg, E_USER_NOTICE);
    }
    
    /**
     * Display warning message
     * 
     * Triggers a warning that will be shown to the user.
     * 
     * @param string $msg The warning message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayWarning(string $msg, bool $center = true): void
    {
        fa_trigger_error($msg, E_USER_WARNING);
    }
}
