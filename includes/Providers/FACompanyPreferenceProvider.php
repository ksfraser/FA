<?php
declare(strict_types=1);

namespace FA\Providers;

use KSF\PrefCache\PreferenceProviderInterface;

/**
 * FrontAccounting Company Preference Provider
 *
 * Reads company preferences from FrontAccounting's SysPrefs global structure.
 * Implements PreferenceProviderInterface for use with PreferenceCache.
 *
 * FA SysPrefs Structure:
 *   $SysPrefs->prefs['use_dimension']
 *   $SysPrefs->prefs['curr_default']
 *   $SysPrefs->prefs['past_due_days']
 *   etc.
 *
 * This adapter makes FA's company preferences compatible with
 * the generic PreferenceCache library.
 *
 * Usage:
 *   $provider = new FACompanyPreferenceProvider();
 *   $cache = new PreferenceCache($provider);
 *   $useDimensions = $cache->get('use_dimension', 0);
 *
 * @package FA\Providers
 */
class FACompanyPreferenceProvider implements PreferenceProviderInterface
{
    /**
     * Get a company preference value by key
     *
     * @param string $key Preference key
     * @param mixed $default Default value if preference not found
     * @return mixed Preference value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        global $SysPrefs;

        // Ensure SysPrefs is loaded
        if (!isset($SysPrefs->prefs)) {
            // Just after first login or reset
            $SysPrefs->refresh();
        }

        return $SysPrefs->prefs[$key] ?? $default;
    }

    /**
     * Get all company preferences at once
     *
     * Returns all company preferences from SysPrefs->prefs array.
     * This enables bulk loading optimization in the cache.
     *
     * @return array<string, mixed> All company preferences keyed by name
     */
    public function getAll(): array
    {
        global $SysPrefs;

        // Ensure SysPrefs is loaded
        if (!isset($SysPrefs->prefs)) {
            // Just after first login or reset
            $SysPrefs->refresh();
        }

        return $SysPrefs->prefs ?? [];
    }

    /**
     * Check if a company preference exists
     *
     * @param string $key Preference key
     * @return bool True if preference exists
     */
    public function has(string $key): bool
    {
        global $SysPrefs;

        // Ensure SysPrefs is loaded
        if (!isset($SysPrefs->prefs)) {
            // Just after first login or reset
            $SysPrefs->refresh();
        }

        return isset($SysPrefs->prefs[$key]);
    }
}