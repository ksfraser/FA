<?php
/**
 * FrontAccounting Timesheet Management Exceptions
 *
 * Custom exceptions for timesheet management functionality.
 *
 * @package FA\Modules\TimesheetManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\TimesheetManagement;

/**
 * Timesheet Exception
 *
 * Base exception for timesheet management operations
 */
class TimesheetException extends \Exception
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}