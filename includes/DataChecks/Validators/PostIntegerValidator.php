<?php

namespace FA\DataChecks\Validators;

/**
 * Validator for POST integer input
 * 
 * Validates $_POST value is integer within optional range
 */
class PostIntegerValidator
{
    /**
     * Validate POST parameter is valid integer within range
     *
     * @param string $postname POST parameter name
     * @param int|null $min Minimum value (inclusive)
     * @param int|null $max Maximum value (inclusive)
     * @return bool True if valid
     */
    public function validate(string $postname, ?int $min = null, ?int $max = null): bool
    {
        if (!isset($_POST[$postname])) {
            return false;
        }
        
        $num = \RequestService::inputNumStatic($postname);
        if (!is_int($num)) {
            return false;
        }
        
        if ($min !== null && $num < $min) {
            return false;
        }
        
        if ($max !== null && $num > $max) {
            return false;
        }
        
        return true;
    }
}
