<?php
declare(strict_types=1);

namespace FA\Services;

/**
 * User Preferences Cache Service
 * 
 * Caches frequently-accessed user preferences to avoid repeated session lookups.
 * Provides event-based cache invalidation when preferences change.
 * 
 * Design Pattern: Singleton with Observer pattern for cache invalidation
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
    private static ?array $cache = null;
    private static array $observers = [];
    
    /**
     * Get price decimal places (cached)
     * 
     * @return int Number of decimal places for price formatting
     */
    public static function getPriceDecimals(): int
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        
        return self::$cache['price_dec'] ?? 2;
    }
    
    /**
     * Get quantity decimal places (cached)
     * 
     * @return int Number of decimal places for quantity formatting
     */
    public static function getQtyDecimals(): int
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        
        return self::$cache['qty_dec'] ?? 2;
    }
    
    /**
     * Get thousands separator (cached)
     * 
     * @return int Thousands separator preference index
     */
    public static function getThousandsSeparator(): int
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        
        return self::$cache['tho_sep'] ?? 0;
    }
    
    /**
     * Get decimal separator (cached)
     * 
     * @return int Decimal separator preference index
     */
    public static function getDecimalSeparator(): int
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        
        return self::$cache['dec_sep'] ?? 0;
    }
    
    /**
     * Get exchange rate decimal places (cached)
     * 
     * @return int Number of decimal places for exchange rate formatting
     */
    public static function getExrateDecimals(): int
    {
        if (self::$cache === null) {
            self::loadCache();
        }
        
        return self::$cache['exrate_dec'] ?? 4;
    }
    
    /**
     * Load cache from session
     * 
     * Populates the cache with current user preferences from the session.
     * This is called automatically on first access.
     * 
     * @return void
     */
    private static function loadCache(): void
    {
        self::$cache = [];
        
        if (isset($_SESSION["wa_current_user"])) {
            $prefs = $_SESSION["wa_current_user"]->prefs;
            
            self::$cache['price_dec'] = $prefs->price_dec();
            self::$cache['qty_dec'] = $prefs->qty_dec();
            self::$cache['tho_sep'] = $prefs->tho_sep();
            self::$cache['dec_sep'] = $prefs->dec_sep();
            self::$cache['exrate_dec'] = $prefs->exrate_dec();
        }
    }
    
    /**
     * Invalidate cache
     * 
     * Clears the preferences cache. Call this when user preferences are updated.
     * Notifies all registered observers about the cache invalidation.
     * 
     * Event Pattern:
     * - Triggers: When user saves preferences, when user logs in/out
     * - Observers: Any service that needs to react to preference changes
     * 
     * @return void
     */
    public static function invalidate(): void
    {
        self::$cache = null;
        
        // Notify observers (event pattern for extensibility)
        foreach (self::$observers as $observer) {
            if (is_callable($observer)) {
                $observer();
            }
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
        self::$observers[] = $observer;
    }
    
    /**
     * Clear all observers (useful for testing)
     * 
     * @return void
     */
    public static function clearObservers(): void
    {
        self::$observers = [];
    }
}
