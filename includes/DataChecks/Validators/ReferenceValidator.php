<?php

namespace FA\DataChecks\Validators;

/**
 * Validator for reference uniqueness and validity
 */
class ReferenceValidator
{
    /**
     * Validate reference is valid and unique
     *
     * @param string $reference Reference to validate
     * @param int $transType Transaction type
     * @param int $transNo Transaction number (0 for new)
     * @param mixed $context Context for reference validation
     * @param mixed $line Line for reference validation
     * @return bool True if valid
     */
    public function validate(
        string $reference,
        int $transType,
        int $transNo = 0,
        $context = null,
        $line = null
    ): bool {
        global $Refs;

        if (!$Refs->is_valid($reference, $transType, $context, $line)) {
            \display_error(\_("The entered reference is invalid."));
            return false;
        }

        if (!$Refs->is_new_reference($reference, $transType, $transNo)) {
            \display_error(\_("The entered reference is already in use."));
            return false;
        }

        return true;
    }
}
