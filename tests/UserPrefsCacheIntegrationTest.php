<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Services\UserPrefsCache;

/**
 * Integration tests for UserPrefsCache invalidation
 * 
 * Verifies that cache is properly invalidated when preferences change
 * through the actual application functions.
 */
class UserPrefsCacheIntegrationTest extends TestCase
{
    private static $originalSession;
    
    public static function setUpBeforeClass(): void
    {
        self::$originalSession = $_SESSION ?? null;
    }
    
    public static function tearDownAfterClass(): void
    {
        if (self::$originalSession !== null) {
            $_SESSION = self::$originalSession;
        } else {
            unset($_SESSION);
        }
    }
    
    protected function setUp(): void
    {
        UserPrefsCache::invalidate();
        UserPrefsCache::clearObservers();
    }
    
    public function testSetUserPrefsInvalidatesCache(): void
    {
        // Create mock user with initial preferences
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 2,
            'qty_dec' => 2,
            'tho_sep' => 0,
            'dec_sep' => 0,
            'exrate_dec' => 4,
        ]);
        
        // Load cache
        $initialPriceDec = UserPrefsCache::getPriceDecimals();
        $this->assertSame(2, $initialPriceDec);
        
        // Simulate preference update through set_user_prefs()
        // The actual function will call update_prefs() and invalidate cache
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 4,
            'qty_dec' => 3,
            'tho_sep' => 1,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        // Call set_user_prefs (which now includes cache invalidation)
        set_user_prefs([
            'price_dec' => 4,
            'qty_dec' => 3,
            'tho_sep' => 1,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        // Cache should be invalidated, so we get fresh value
        $newPriceDec = UserPrefsCache::getPriceDecimals();
        $this->assertSame(4, $newPriceDec, 'Cache should be invalidated by set_user_prefs()');
    }
    
    public function testObserverNotifiedOnPreferenceChange(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 2,
            'qty_dec' => 2,
            'tho_sep' => 0,
            'dec_sep' => 0,
            'exrate_dec' => 4,
        ]);
        
        $observerCalled = false;
        
        // Register observer to detect cache invalidation
        UserPrefsCache::registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });
        
        // Update preferences (which triggers invalidation)
        set_user_prefs([
            'price_dec' => 4,
            'qty_dec' => 3,
        ]);
        
        $this->assertTrue($observerCalled, 'Observer should be notified when preferences change');
    }
    
    public function testMultiplePreferenceAccessesUseCache(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 2,
            'tho_sep' => 1,
            'dec_sep' => 1,
            'exrate_dec' => 5,
        ]);
        
        $callCount = 0;
        
        // Register observer to count invalidations
        UserPrefsCache::registerObserver(function() use (&$callCount) {
            $callCount++;
        });
        
        // Access preferences multiple times (simulating real usage)
        for ($i = 0; $i < 100; $i++) {
            $priceDec = UserPrefsCache::getPriceDecimals();
            $qtyDec = UserPrefsCache::getQtyDecimals();
            $thoSep = UserPrefsCache::getThousandsSeparator();
            $decSep = UserPrefsCache::getDecimalSeparator();
            $exrateDec = UserPrefsCache::getExrateDecimals();
        }
        
        // All values should be cached - no invalidations
        $this->assertSame(0, $callCount, 'Cache should not be invalidated during repeated access');
        
        // Now trigger one update
        set_user_prefs(['price_dec' => 4]);
        
        $this->assertSame(1, $callCount, 'Exactly one cache invalidation should occur on preference update');
    }
    
    public function testCacheInvalidationOnLogout(): void
    {
        $_SESSION["wa_current_user"] = $this->createMockUser([
            'price_dec' => 3,
            'qty_dec' => 4,
            'tho_sep' => 2,
            'dec_sep' => 1,
            'exrate_dec' => 6,
        ]);
        
        // Load cache
        $priceDec = UserPrefsCache::getPriceDecimals();
        $this->assertSame(3, $priceDec);
        
        $invalidationCalled = false;
        
        UserPrefsCache::registerObserver(function() use (&$invalidationCalled) {
            $invalidationCalled = true;
        });
        
        // Simulate logout (logout.php calls invalidate before session_unset)
        UserPrefsCache::invalidate();
        
        $this->assertTrue($invalidationCalled, 'Cache should be invalidated on logout');
    }
    
    /**
     * Create mock user object with preferences and update_prefs method
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
        };
        
        $mockUser = new class($mockPrefs) {
            public $prefs;
            
            public function __construct($prefs) {
                $this->prefs = $prefs;
            }
            
            public function update_prefs($newPrefs) {
                // Mock implementation - in real code this updates $this->prefs
                // For testing, we just need it to exist
            }
        };
        
        $mockUser->prefs = $mockPrefs;
        
        return $mockUser;
    }
}
