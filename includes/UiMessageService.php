<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * UI Message Service
 * 
 * Handles user interface messages, errors, warnings, and notifications.
 * Replaces legacy display_error(), display_notification(), display_warning() functions.
 * 
 * Directly adds messages to the global $messages array that is displayed by fmt_errors().
 * This bypasses trigger_error() and error_handler() for better performance and clarity.
 */
class UiMessageService
{
    /**
     * Display error message
     * 
     * Directly adds an error message to the global $messages array.
     * Error messages are displayed by fmt_errors() with the 'err_msg' CSS class.
     * 
     * @param string $msg The error message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayError(string $msg, bool $center = true): void
    {
        self::addMessage(E_USER_ERROR, $msg);
    }
    
    /**
     * Display notification message
     * 
     * Directly adds a notification message to the global $messages array.
     * Notification messages are displayed by fmt_errors() with the 'note_msg' CSS class.
     * 
     * @param string $msg The notification message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayNotification(string $msg, bool $center = true): void
    {
        self::addMessage(E_USER_NOTICE, $msg);
    }
    
    /**
     * Display warning message
     * 
     * Directly adds a warning message to the global $messages array.
     * Warning messages are displayed by fmt_errors() with the 'warn_msg' CSS class.
     * 
     * @param string $msg The warning message to display
     * @param bool $center Whether to center the message (legacy parameter, kept for compatibility)
     * @return void
     */
    public static function displayWarning(string $msg, bool $center = true): void
    {
        self::addMessage(E_USER_WARNING, $msg);
    }
    
    /**
     * Add a message directly to the global $messages array
     * 
     * This is the core implementation that replaces error_handler() logic.
     * Messages are stored as: [$errno, $errstr, $file, $line, $backtrace]
     * 
     * @param int $errno The error level (E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE)
     * @param string $errstr The message text
     * @return void
     */
    private static function addMessage(int $errno, string $errstr): void
    {
        global $messages, $SysPrefs, $cur_error_level;
        
        // Get backtrace if debug mode is enabled
        $bt = isset($SysPrefs) && $SysPrefs->go_debug > 1 
            ? get_backtrace(true, 2) // Skip 2 levels: addMessage and display* method
            : [];
        
        // Get caller information (the display* method that called us)
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        $caller = $trace[2] ?? $trace[1] ?? ['file' => __FILE__, 'line' => __LINE__];
        $file = $caller['file'] ?? __FILE__;
        $line = $caller['line'] ?? __LINE__;
        
        // Only add message if error reporting allows it
        if ($cur_error_level == error_reporting()) {
            if ($errno & $cur_error_level) {
                // Suppress duplicated errors
                $message = [$errno, $errstr, $file, $line, $bt];
                if (!in_array($message, $messages)) {
                    $messages[] = $message;
                }
            }
            else if ($errno & ~E_NOTICE && $errstr != '') {
                // Log all not displayed messages
                $user = @$_SESSION["wa_current_user"]->loginname ?? 'unknown';
                $context = isset($SysPrefs) && !$SysPrefs->db_ok ? '[before upgrade]' : '';
                error_log(user_company() . ":$user:" . basename($file) . ":$line:$context $errstr");
            }
        }
    }
}
