<?php
declare(strict_types=1);

namespace FA\Services;

use FA\Library\Cache\PreferenceCache;
use FA\Providers\FASessionPreferenceProvider;

/**
 * FrontAccounting User Preferences Cache
 * 
 * FA-specific facade for the generic PreferenceCache library.
 * Provides backwards-compatible static API while using the reusable cache underneath.
 * 
 * This is a FACADE/ADAPTER that wraps the generic library for FA convenience.
 * The actual caching logic lives in FA\Library\Cache\PreferenceCache.
 * 
 * Architecture:
 *   UserPrefsCache (this class)
 *     ↓ uses
 *   PreferenceCache (generic library)
 *     ↓ uses
 *   FASessionPreferenceProvider (FA-specific adapter)
 *     ↓ reads from
 *   $_SESSION["wa_current_user"]->prefs (FA data structure)
 * 
 * Performance Impact:
 * - user_price_dec() called 191+ times per request → 1 lookup + 190 cache hits
 * - user_tho_sep() called 378+ times per request → 1 lookup + 377 cache hits
 * - user_dec_sep() called 378+ times per request → 1 lookup + 377 cache hits
 * 
 * Usage:
 *   $priceDecimals = UserPrefsCache::getPriceDecimals();
 *   UserPrefsCache::invalidate(); // When user changes preferences
 */
class UserPrefsCache
{
    private static ?PreferenceCache $cache = null;
    
    /**
     * Get the underlying cache instance
     * 
     * Lazy initialization of the cache with FA session provider.
     * 
     * @return PreferenceCache
     */
    private static function getCache(): PreferenceCache
    {
        if (self::$cache === null) {
            $provider = new FASessionPreferenceProvider();
            self::$cache = new PreferenceCache($provider);
        }
        
        return self::$cache;
    }
    
    /**
     * Get price decimal places (cached)
     * 
     * @return int Number of decimal places for price formatting
     */
    public static function getPriceDecimals(): int
    {
        return (int)self::getCache()->get('price_dec', 2);
    }
    
    /**
     * Get quantity decimal places (cached)
     * 
     * @return int Number of decimal places for quantity formatting
     */
    public static function getQtyDecimals(): int
    {
        return (int)self::getCache()->get('qty_dec', 2);
    }
    
    /**
     * Get thousands separator (cached)
     * 
     * @return int Thousands separator preference index
     */
    public static function getThousandsSeparator(): int
    {
        return (int)self::getCache()->get('tho_sep', 0);
    }
    
    /**
     * Get decimal separator (cached)
     * 
     * @return int Decimal separator preference index
     */
    public static function getDecimalSeparator(): int
    {
        return (int)self::getCache()->get('dec_sep', 0);
    }
    
    /**
     * Get exchange rate decimal places (cached)
     * 
     * @return int Number of decimal places for exchange rate formatting
     */
    public static function getExrateDecimals(): int
    {
        return (int)self::getCache()->get('exrate_dec', 4);
    }
    
    /**
     * Invalidate cache
     * 
     * Clears the preferences cache. Call this when user preferences are updated.
     * Notifies all registered observers about the cache invalidation.
     * 
     * @return void
     */
    public static function invalidate(): void
    {
        if (self::$cache !== null) {
            self::$cache->invalidate();
        }
    }
    
    /**
     * Register cache invalidation observer
     * 
     * Allows other services to be notified when the cache is invalidated.
     * This implements the Observer pattern for loose coupling.
     * 
     * Example:
     *   UserPrefsCache::registerObserver(function() {
     *       // Clear derived caches, reset formatters, etc.
     *   });
     * 
     * @param callable $observer Callback function to execute on cache invalidation
     * @return void
     */
    public static function registerObserver(callable $observer): void
    {
        self::getCache()->registerObserver($observer);
    }
    
    /**
     * Clear all observers (useful for testing)
     * 
     * @return void
     */
    public static function clearObservers(): void
    {
        if (self::$cache !== null) {
            self::$cache->clearObservers();
        }
    }
    
    /**
     * Get raw value by key (for extensibility)
     * 
     * Allows access to any preference without predefined methods.
     * Useful for testing or accessing preferences not in the standard API.
     * 
     * @param string $key Preference key
     * @param mixed $default Default value
     * @return mixed Preference value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return self::getCache()->get($key, $default);
    }
}
