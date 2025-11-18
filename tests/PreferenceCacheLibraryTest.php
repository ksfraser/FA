<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use KSF\PrefCache\PreferenceCache;
use KSF\PrefCache\PreferenceProviderInterface;

/**
 * Tests for generic PreferenceCache library
 * 
 * Verifies the reusable cache works with any provider implementation.
 */
class PreferenceCacheLibraryTest extends TestCase
{
    public function testCacheLoadsFromProvider(): void
    {
        $provider = $this->createMockProvider([
            'color' => 'blue',
            'size' => 'large',
        ]);
        
        $cache = new PreferenceCache($provider);
        
        $this->assertSame('blue', $cache->get('color'));
        $this->assertSame('large', $cache->get('size'));
    }
    
    public function testCacheReturnsDefaultForMissingKey(): void
    {
        $provider = $this->createMockProvider(['color' => 'blue']);
        $cache = new PreferenceCache($provider);
        
        $result = $cache->get('missing_key', 'default_value');
        
        $this->assertSame('default_value', $result);
    }
    
    public function testCacheUsesProviderGetAllForBulkLoad(): void
    {
        $provider = $this->createMockProvider([
            'pref1' => 'value1',
            'pref2' => 'value2',
            'pref3' => 'value3',
        ]);
        
        $cache = new PreferenceCache($provider);
        
        // First access triggers bulk load
        $cache->get('pref1');
        
        // All values should be cached now
        $all = $cache->getAll();
        
        $this->assertCount(3, $all);
        $this->assertSame('value1', $all['pref1']);
        $this->assertSame('value2', $all['pref2']);
        $this->assertSame('value3', $all['pref3']);
    }
    
    public function testHasReturnsTrueForExistingKey(): void
    {
        $provider = $this->createMockProvider(['color' => 'red']);
        $cache = new PreferenceCache($provider);
        
        $this->assertTrue($cache->has('color'));
        $this->assertFalse($cache->has('missing'));
    }
    
    public function testInvalidateClearsCache(): void
    {
        // Create provider that counts calls using a container object
        $counter = new class {
            public int $count = 0;
        };
        
        $provider = new class($counter) implements PreferenceProviderInterface {
            private $counter;
            
            public function __construct($counter) {
                $this->counter = $counter;
            }
            
            public function get(string $key, mixed $default = null): mixed {
                $this->counter->count++;
                return 'value';
            }
            
            public function getAll(): array {
                $this->counter->count++;
                return ['key' => 'value'];
            }
            
            public function has(string $key): bool {
                return true;
            }
        };
        
        $cache = new PreferenceCache($provider);
        
        // First access - loads cache
        $cache->get('key');
        $firstCallCount = $counter->count;
        
        // Second access - uses cache (no new call)
        $cache->get('key');
        $this->assertSame($firstCallCount, $counter->count, 'Cache should be used');
        
        // Invalidate
        $cache->invalidate();
        
        // Third access - reloads cache
        $cache->get('key');
        $this->assertGreaterThan($firstCallCount, $counter->count, 'Cache should be reloaded after invalidation');
    }
    
    public function testObserverNotifiedOnInvalidation(): void
    {
        $provider = $this->createMockProvider(['key' => 'value']);
        $cache = new PreferenceCache($provider);
        
        $observerCalled = false;
        
        $cache->registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });
        
        $cache->invalidate();
        
        $this->assertTrue($observerCalled);
    }
    
    public function testMultipleObserversNotified(): void
    {
        $provider = $this->createMockProvider(['key' => 'value']);
        $cache = new PreferenceCache($provider);
        
        $observer1Called = false;
        $observer2Called = false;
        
        $cache->registerObserver(function() use (&$observer1Called) {
            $observer1Called = true;
        });
        
        $cache->registerObserver(function() use (&$observer2Called) {
            $observer2Called = true;
        });
        
        $cache->invalidate();
        
        $this->assertTrue($observer1Called);
        $this->assertTrue($observer2Called);
    }
    
    public function testClearObserversRemovesAllObservers(): void
    {
        $provider = $this->createMockProvider(['key' => 'value']);
        $cache = new PreferenceCache($provider);
        
        $observerCalled = false;
        
        $cache->registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });
        
        $cache->clearObservers();
        $cache->invalidate();
        
        $this->assertFalse($observerCalled);
    }
    
    public function testGetProviderReturnsProvider(): void
    {
        $provider = $this->createMockProvider(['key' => 'value']);
        $cache = new PreferenceCache($provider);
        
        $this->assertSame($provider, $cache->getProvider());
    }
    
    /**
     * Create a simple mock provider for testing
     * 
     * @param array $data Preference data
     * @return PreferenceProviderInterface
     */
    private function createMockProvider(array $data): PreferenceProviderInterface
    {
        return new class($data) implements PreferenceProviderInterface {
            private array $data;
            
            public function __construct(array $data) {
                $this->data = $data;
            }
            
            public function get(string $key, mixed $default = null): mixed {
                return $this->data[$key] ?? $default;
            }
            
            public function getAll(): array {
                return $this->data;
            }
            
            public function has(string $key): bool {
                return array_key_exists($key, $this->data);
            }
        };
    }
}
