<?php

namespace FA\DataChecks\Validators;

use FA\Contracts\ValidationErrorHandlerInterface;
use FA\Services\CompanyPrefsService;

/**
 * Validator for system preference existence
 */
class SystemPreferenceValidator
{
    private ValidationErrorHandlerInterface $errorHandler;

    public function __construct(ValidationErrorHandlerInterface $errorHandler)
    {
        $this->errorHandler = $errorHandler;
    }

    /**
     * Validate system preference is set (not empty)
     *
     * @param string $name Preference name
     * @param string $msg Error message with optional link
     * @param string $empty Value considered empty (default '')
     * @return void
     */
    public function validate(string $name, string $msg, string $empty = ''): void
    {
        if (CompanyPrefsService::getCompanyPref($name) === $empty) {
            $errorMsg = \menu_link("/admin/gl_setup.php", $msg);
            $this->errorHandler->handleValidationError($errorMsg);
        }
    }
}
