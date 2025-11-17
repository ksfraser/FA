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
}
