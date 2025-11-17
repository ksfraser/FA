<?php
/**
 * Company Preferences Interface
 *
 * Abstraction for company preferences access
 * Enables dependency injection and testing
 *
 * @package FA\Contracts
 */

namespace FA\Contracts;

interface CompanyPreferencesInterface
{
    /**
     * Get a company preference value
     *
     * @param string $key Preference key
     * @return mixed Preference value
     */
    public function get(string $key);
    
    /**
     * Set a company preference value
     *
     * @param string $key Preference key
     * @param mixed $value Preference value
     * @return void
     */
    public function set(string $key, $value): void;
}
