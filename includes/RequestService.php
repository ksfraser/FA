<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * Request/Form Data Service
 * 
 * Handles HTTP request data retrieval and validation.
 * Provides centralized access to POST/GET parameters with type conversion.
 * 
 * This replaces the legacy get_post() and input_num() functions with proper OOP methods.
 */
class RequestService
{
    /**
     * Get POST parameter value with optional default
     * 
     * Can handle single values or arrays of values.
     * Numeric defaults trigger automatic conversion from user format to POSIX.
     * 
     * @param string|array $name Parameter name or array of name=>default pairs
     * @param mixed $dflt Default value if parameter not set
     * @return mixed Parameter value or default
     */
    public static function getPostStatic(string|array $name, mixed $dflt = ''): mixed
    {
        if (is_array($name)) {
            $ret = array();
            foreach($name as $key => $dflt) {
                if (!is_numeric($key)) {
                    $ret[$key] = is_numeric($dflt) ? self::inputNumStatic($key, $dflt) : self::getPostStatic($key, $dflt);
                } else {
                    $ret[$dflt] = self::getPostStatic($dflt, null);
                }
            }
            return $ret;
        } else {
            return is_float($dflt) ? self::inputNumStatic($name, $dflt) :
                    ((!isset($_POST[$name])) ? $dflt : $_POST[$name]);
        }
    }
    
    /**
     * Get numeric value from POST parameter with user format conversion
     * 
     * Reads a numeric value from user-formatted input (e.g., "1,234.56")
     * and converts it to POSIX format (e.g., 1234.56).
     * 
     * @param string|null $postname Parameter name
     * @param float|int $dflt Default value if parameter not set or empty
     * @return float|int Numeric value in POSIX format
     */
    public static function inputNumStatic(?string $postname = null, float|int $dflt = 0): float|int
    {
        if (!isset($_POST[$postname]) || $_POST[$postname] == "") {
            return $dflt;
        }

        return user_numeric($_POST[$postname]);
    }
    
    /**
     * Check if POST parameter has a value (checkbox/boolean check)
     * 
     * Returns 1 if the POST parameter is set and non-empty, 0 otherwise.
     * Can handle arrays of parameter names.
     * 
     * @param string|array $name Parameter name or array of parameter names
     * @return int|array 1 if set and non-empty, 0 otherwise (or array of results)
     */
    public static function checkValueStatic(string|array $name): int|array
    {
        if (is_array($name)) {
            $ret = array();
            foreach($name as $key) {
                $ret[$key] = self::checkValueStatic($key);
            }
            return $ret;
        } else {
            return (empty($_POST[$name]) ? 0 : 1);
        }
    }
}
