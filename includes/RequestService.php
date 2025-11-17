<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * Request/Form Data Service
 * 
 * Handles HTTP request data retrieval and validation.
 * Provides centralized access to POST/GET parameters with type conversion.
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
    public function getPost(string|array $name, mixed $dflt = ''): mixed
    {
        return \RequestService::getPostStatic($name, $dflt);
    }
    
    /**
     * Static wrapper for get_post()
     * 
     * @param string|array $name Parameter name or array of name=>default pairs
     * @param mixed $dflt Default value if parameter not set
     * @return mixed Parameter value or default
     */
    public static function getPostStatic(string|array $name, mixed $dflt = ''): mixed
    {
        return \get_post($name, $dflt);
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
    public function inputNum(?string $postname = null, float|int $dflt = 0): float|int
    {
        return \input_num($postname, $dflt);
    }
    
    /**
     * Static wrapper for input_num()
     * 
     * @param string|null $postname Parameter name
     * @param float|int $dflt Default value if parameter not set or empty
     * @return float|int Numeric value in POSIX format
     */
    public static function inputNumStatic(?string $postname = null, float|int $dflt = 0): float|int
    {
        return \input_num($postname, $dflt);
    }
}
