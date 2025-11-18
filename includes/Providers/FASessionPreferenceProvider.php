<?php
declare(strict_types=1);

namespace FA\Providers;

use KSF\PrefCache\PreferenceProviderInterface;

/**
 * FrontAccounting Session Preference Provider
 * 
 * Reads user preferences from FrontAccounting's session structure.
 * Implements PreferenceProviderInterface for use with PreferenceCache.
 * 
 * FA Session Structure:
 *   $_SESSION["wa_current_user"]->prefs->price_dec()
 *   $_SESSION["wa_current_user"]->prefs->qty_dec()
 *   $_SESSION["wa_current_user"]->prefs->tho_sep()
 *   etc.
 * 
 * This adapter makes FA's session-based preferences compatible with
 * the generic PreferenceCache library.
 * 
 * Usage:
 *   $provider = new FASessionPreferenceProvider();
 *   $cache = new PreferenceCache($provider);
 *   $priceDecimals = $cache->get('price_dec', 2);
 * 
 * @package FA\Providers
 */
class FASessionPreferenceProvider implements PreferenceProviderInterface
{
    /**
     * Mapping of preference keys to session accessor methods
     * 
     * @var array<string, string>
     */
    private const PREFERENCE_METHODS = [
        'price_dec' => 'price_dec',
        'qty_dec' => 'qty_dec',
        'tho_sep' => 'tho_sep',
        'dec_sep' => 'dec_sep',
        'exrate_dec' => 'exrate_dec',
        'language' => 'language',
        'date_format' => 'date_format',
        'date_sep' => 'date_sep',
    ];
    
    /**
     * Default values for each preference
     * 
     * @var array<string, mixed>
     */
    private const DEFAULTS = [
        'price_dec' => 2,
        'qty_dec' => 2,
        'tho_sep' => 0,
        'dec_sep' => 0,
        'exrate_dec' => 4,
        'language' => 'en_US',
        'date_format' => 0,
        'date_sep' => 0,
    ];
    
    /**
     * Get a preference value by key
     * 
     * @param string $key Preference key (e.g., 'price_dec')
     * @param mixed $default Default value if preference not found
     * @return mixed Preference value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Use provided default, or fall back to class default, or null
        $defaultValue = $default ?? (self::DEFAULTS[$key] ?? null);
        
        // Check if user is logged in
        if (!isset($_SESSION["wa_current_user"])) {
            return $defaultValue;
        }
        
        // Check if preference method exists
        $method = self::PREFERENCE_METHODS[$key] ?? null;
        if ($method === null) {
            return $defaultValue;
        }
        
        // Call the preference method
        try {
            $prefs = $_SESSION["wa_current_user"]->prefs;
            if (method_exists($prefs, $method)) {
                return $prefs->$method();
            }
        } catch (\Throwable $e) {
            // Gracefully handle any errors
            return $defaultValue;
        }
        
        return $defaultValue;
    }
    
    /**
     * Get all preferences at once
     * 
     * Optimized bulk loading of all FA preferences.
     * 
     * @return array<string, mixed> All preferences keyed by name
     */
    public function getAll(): array
    {
        if (!isset($_SESSION["wa_current_user"])) {
            return self::DEFAULTS;
        }
        
        $prefs = [];
        $sessionPrefs = $_SESSION["wa_current_user"]->prefs;
        
        foreach (self::PREFERENCE_METHODS as $key => $method) {
            try {
                if (method_exists($sessionPrefs, $method)) {
                    $prefs[$key] = $sessionPrefs->$method();
                } else {
                    $prefs[$key] = self::DEFAULTS[$key] ?? null;
                }
            } catch (\Throwable $e) {
                $prefs[$key] = self::DEFAULTS[$key] ?? null;
            }
        }
        
        return $prefs;
    }
    
    /**
     * Check if a preference exists
     * 
     * @param string $key Preference key
     * @return bool True if preference exists
     */
    public function has(string $key): bool
    {
        return isset(self::PREFERENCE_METHODS[$key]);
    }
    
    /**
     * Get available preference keys
     * 
     * @return array<string> List of available preference keys
     */
    public function getAvailableKeys(): array
    {
        return array_keys(self::PREFERENCE_METHODS);
    }
}
