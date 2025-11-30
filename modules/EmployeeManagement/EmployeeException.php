<?php
/**
 * FrontAccounting Employee Management Exceptions
 *
 * Custom exceptions for employee management functionality.
 *
 * @package FA\Modules\EmployeeManagement
 * @version 1.0.0
 * @author FrontAccounting Team
 * @license GPL-3.0
 */

namespace FA\Modules\EmployeeManagement;

/**
 * Employee Exception
 *
 * Base exception for employee management operations
 */
class EmployeeException extends \Exception
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}