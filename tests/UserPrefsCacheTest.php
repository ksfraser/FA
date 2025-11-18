<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Services\UserPrefsCache;

/**
 * Test suite for UserPrefsCache
 * 
 * Verifies:
 * - Cache loads user preferences correctly
 * - Cache returns default values when no user is logged in
 * - Cache invalidation works properly
 * - Observer pattern notifies listeners on invalidation
 * - Multiple cache hits don't trigger reloads
 */
class UserPrefsCacheTest extends TestCase
{
    private static $originalSession;
    
    public static function setUpBeforeClass(): void
    {
        // Save original session state
        self::$originalSession = $_SESSION ?? null;
    }
    
    public static function tearDownAfterClass(): void
    {
        // Restore original session state
        if (self::$originalSession !== null) {
            $_SESSION = self::$originalSession;
        } else {
            unset($_SESSION);
        }
    }
    
    protected function setUp(): void
    {
        // Clear cache before each test
        UserPrefsCache::invalidate();
        UserPrefsCache::clearObservers();
    }
    
    public function testGetPriceDecimalsWithUserLoggedIn(): void
    {
        // Setup mock user with preferences
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 1,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        $result = UserPrefsCache::getPriceDecimals();
        
        $this->assertSame(3, $result);
    }
    
    public function testGetQtyDecimalsWithUserLoggedIn(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 1,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        $result = UserPrefsCache::getQtyDecimals();
        
        $this->assertSame(4, $result);
    }
    
    public function testGetThousandsSeparatorWithUserLoggedIn(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        $result = UserPrefsCache::getThousandsSeparator();
        
        $this->assertSame(2, $result);
    }
    
    public function testGetDecimalSeparatorWithUserLoggedIn(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        $result = UserPrefsCache::getDecimalSeparator();
        
        $this->assertSame(1, $result);
    }
    
    public function testGetExrateDecimalsWithUserLoggedIn(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 5,
        ]);
        
        $result = UserPrefsCache::getExrateDecimals();
        
        $this->assertSame(5, $result);
    }
    
    public function testDefaultValuesWhenNoUserLoggedIn(): void
    {
        // Ensure no user is logged in
        unset($_SESSION["wa_current_user"]);
        
        $this->assertSame(2, UserPrefsCache::getPriceDecimals(), 'Default price decimals should be 2');
        $this->assertSame(2, UserPrefsCache::getQtyDecimals(), 'Default qty decimals should be 2');
        $this->assertSame(0, UserPrefsCache::getThousandsSeparator(), 'Default thousands separator should be 0');
        $this->assertSame(0, UserPrefsCache::getDecimalSeparator(), 'Default decimal separator should be 0');
        $this->assertSame(4, UserPrefsCache::getExrateDecimals(), 'Default exrate decimals should be 4');
    }
    
    public function testCacheReturnsConsistentValuesWithoutReloading(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        // First call - loads cache
        $result1 = UserPrefsCache::getPriceDecimals();
        
        // Change session data (should not affect cache until invalidated)
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 5,
            'qty_dec' => 6,
            'tho_sep' => 3,
            'dec_sep' => 2,
            'exrate_dec' => 8,
        ]);
        
        // Second call - should return cached value (3), not new value (5)
        $result2 = UserPrefsCache::getPriceDecimals();
        
        $this->assertSame(3, $result1);
        $this->assertSame(3, $result2, 'Cache should return original value until invalidated');
    }
    
    public function testInvalidateClearsCache(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        // Load cache
        $result1 = UserPrefsCache::getPriceDecimals();
        $this->assertSame(3, $result1);
        
        // Change session data
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 5,
            'qty_dec' => 6,
            'tho_sep' => 3,
            'dec_sep' => 2,
            'exrate_dec' => 8,
        ]);
        
        // Invalidate cache
        UserPrefsCache::invalidate();
        
        // Should reload and return new value
        $result2 = UserPrefsCache::getPriceDecimals();
        $this->assertSame(5, $result2, 'After invalidation, cache should reload with new values');
    }
    
    public function testObserverIsNotifiedOnInvalidation(): void
    {
        $observerCalled = false;
        
        // Register observer
        UserPrefsCache::registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });
        
        // Trigger invalidation
        UserPrefsCache::invalidate();
        
        $this->assertTrue($observerCalled, 'Observer should be called on cache invalidation');
    }
    
    public function testMultipleObserversAreNotified(): void
    {
        $observer1Called = false;
        $observer2Called = false;
        $observer3Called = false;
        
        // Register multiple observers
        UserPrefsCache::registerObserver(function() use (&$observer1Called) {
            $observer1Called = true;
        });
        UserPrefsCache::registerObserver(function() use (&$observer2Called) {
            $observer2Called = true;
        });
        UserPrefsCache::registerObserver(function() use (&$observer3Called) {
            $observer3Called = true;
        });
        
        // Trigger invalidation
        UserPrefsCache::invalidate();
        
        $this->assertTrue($observer1Called, 'Observer 1 should be called');
        $this->assertTrue($observer2Called, 'Observer 2 should be called');
        $this->assertTrue($observer3Called, 'Observer 3 should be called');
    }
    
    public function testClearObserversRemovesAllObservers(): void
    {
        $observerCalled = false;
        
        // Register observer
        UserPrefsCache::registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });
        
        // Clear observers
        UserPrefsCache::clearObservers();
        
        // Trigger invalidation
        UserPrefsCache::invalidate();
        
        $this->assertFalse($observerCalled, 'Observer should not be called after clearObservers');
    }
    
    /**
     * Create mock user object with preferences
     * 
     * @param array $prefs Preference values
     * @return object Mock user with prefs
     */
    private function createMockUser(array $prefs): object
    {
        $mockPrefs = new class($prefs) {
            private $prefs;
            
            public function __construct(array $prefs) {
                $this->prefs = $prefs;
            }
            
            public function price_dec() {
                return $this->prefs['price_dec'];
            }
            
            public function qty_dec() {
                return $this->prefs['qty_dec'];
            }
            
            public function tho_sep() {
                return $this->prefs['tho_sep'];
            }
            
            public function dec_sep() {
                return $this->prefs['dec_sep'];
            }
            
            public function exrate_dec() {
                return $this->prefs['exrate_dec'];
            }
            
            public function percent_dec() {
                return $this->prefs['percent_dec'] ?? 1;
            }
        };
        
        return (object)['prefs' => $mockPrefs];
    }
}
