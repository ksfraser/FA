<?php

namespace FA\DataChecks\Validators;

/**
 * Validator for POST numeric input
 * 
 * Validates $_POST value is number within optional range
 */
class PostNumericValidator
{
    /**
     * Validate POST parameter is valid number within range
     *
     * @param string $postname POST parameter name
     * @param float|null $min Minimum value (inclusive)
     * @param float|null $max Maximum value (inclusive)
     * @param float $default Default value if not set
     * @return bool True if valid
     */
    public function validate(string $postname, ?float $min = null, ?float $max = null, float $default = 0): bool
    {
        if (!isset($_POST[$postname])) {
            return false;
        }
        
        $num = \input_num($postname, $default);
        if ($num === false || $num === null) {
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
