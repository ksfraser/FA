<?php
declare(strict_types=1);

namespace FA\Services;

use KSF\PrefCache\PreferenceCache;
use FA\Providers\FACompanyPreferenceProvider;

/**
 * FrontAccounting Company Preferences Cache Service
 *
 * FA-specific facade for the generic PreferenceCache library.
 * Provides backwards-compatible static API while using the reusable cache underneath.
 *
 * This is a FACADE/ADAPTER that wraps the generic library for FA convenience.
 * The actual caching logic lives in KSF\PrefCache\PreferenceCache.
 *
 * Architecture:
 *   CompanyPrefsService (this class)
 *     ↓ uses
 *   PreferenceCache (generic library)
 *     ↓ uses
 *   FACompanyPreferenceProvider (FA-specific adapter)
 *     ↓ reads from
 *   $SysPrefs->prefs (FA data structure)
 *
 * Performance Impact:
 * - get_company_pref() called 50+ times per request → 1 lookup + 49 cache hits
 * - Common preferences like 'use_dimension', 'curr_default' accessed frequently
 *
 * Usage:
 *   $useDimensions = CompanyPrefsService::getUseDimensions();
 *   CompanyPrefsService::invalidate(); // When company preferences change
 */
class CompanyPrefsService
{
    private static ?PreferenceCache $cache = null;

    /**
     * Get the underlying cache instance
     *
     * Lazy initialization of the cache with FA company preference provider.
     *
     * @return PreferenceCache
     */
    private static function getCache(): PreferenceCache
    {
        if (self::$cache === null) {
            $provider = new FACompanyPreferenceProvider();
            self::$cache = new PreferenceCache($provider);
        }

        return self::$cache;
    }

    /**
     * Get use dimensions preference (cached)
     *
     * @return int Whether dimensions are enabled (0/1)
     */
    public static function getUseDimensions(): int
    {
        return (int)self::getCache()->get('use_dimension', 0);
    }

    /**
     * Get default currency (cached)
     *
     * @return string Default company currency code
     */
    public static function getDefaultCurrency(): string
    {
        return self::getCache()->get('curr_default', 'USD');
    }

    /**
     * Get past due days (cached)
     *
     * @return int Number of days for past due calculations
     */
    public static function getPastDueDays(): int
    {
        return (int)self::getCache()->get('past_due_days', 30);
    }

    /**
     * Get any company preference by key (cached)
     *
     * @param string $key Preference key
     * @param mixed $default Default value if not found
     * @return mixed Preference value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getCache()->get($key, $default);
    }

    /**
     * Invalidate cache
     *
     * Clears the company preferences cache. Call this when company preferences are updated.
     * Notifies all registered observers about the cache invalidation.
     *
     * Event Pattern:
     * - Triggers: When company saves preferences, when company settings change
     * - Observers: Any service that needs to react to preference changes
     *
     * @return void
     */
    public static function invalidate(): void
    {
        self::getCache()->invalidate();
    }

    /**
     * Register cache invalidation observer
     *
     * @param callable $observer Function to call when cache is invalidated
     * @return void
     */
    public static function registerObserver(callable $observer): void
    {
        self::getCache()->registerObserver($observer);
    }
}