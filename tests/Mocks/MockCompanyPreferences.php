<?php
/**
 * Mock Company Preferences
 *
 * Test double for company preferences
 * Implements CompanyPreferencesInterface for testing
 *
 * @package FA\Tests\Mocks
 */

namespace FA\Tests\Mocks;

use FA\Contracts\CompanyPreferencesInterface;

class MockCompanyPreferences implements CompanyPreferencesInterface
{
    private array $preferences = [];
    
    public function __construct(array $defaults = [])
    {
        $this->preferences = array_merge([
            'curr_default' => 'USD',
            'exchange_diff_act' => '1000',
            'deferred_income_act' => '2000'
        ], $defaults);
    }
    
    public function get(string $key)
    {
        return $this->preferences[$key] ?? null;
    }
    
    public function set(string $key, $value): void
    {
        $this->preferences[$key] = $value;
    }
}
