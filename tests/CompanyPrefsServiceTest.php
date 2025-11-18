<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/CompanyPrefsService.php';

use PHPUnit\Framework\TestCase;
use FA\Services\CompanyPrefsService;

/**
 * Test CompanyPrefsService
 *
 * Tests the company preferences caching service.
 * Verifies cache loading, invalidation, and observer pattern.
 */
class CompanyPrefsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear cache before each test
        CompanyPrefsService::invalidate();

        // Mock global SysPrefs
        global $SysPrefs;
        $SysPrefs = new stdClass();
        $SysPrefs->prefs = [
            'use_dimension' => '1',
            'curr_default' => 'USD',
            'past_due_days' => '30',
            'company_name' => 'Test Company'
        ];
    }

    protected function tearDown(): void
    {
        // Clean up global after each test
        global $SysPrefs;
        unset($SysPrefs);
    }

    public function testGetUseDimensions(): void
    {
        $result = CompanyPrefsService::getUseDimensions();
        $this->assertEquals(1, $result);
    }

    public function testGetDefaultCurrency(): void
    {
        $result = CompanyPrefsService::getDefaultCurrency();
        $this->assertEquals('USD', $result);
    }

    public function testGetPastDueDays(): void
    {
        $result = CompanyPrefsService::getPastDueDays();
        $this->assertEquals(30, $result);
    }

    public function testGetGenericPreference(): void
    {
        $result = CompanyPrefsService::get('company_name');
        $this->assertEquals('Test Company', $result);
    }

    public function testGetWithDefault(): void
    {
        $result = CompanyPrefsService::get('nonexistent', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    public function testCachePersistence(): void
    {
        // First call loads cache
        CompanyPrefsService::getUseDimensions();

        // Modify global (should not affect cached value)
        global $SysPrefs;
        $SysPrefs->prefs['use_dimension'] = '0';

        // Second call should return cached value
        $result = CompanyPrefsService::getUseDimensions();
        $this->assertEquals(1, $result);
    }

    public function testInvalidateCache(): void
    {
        // Load cache
        CompanyPrefsService::getUseDimensions();

        // Modify global
        global $SysPrefs;
        $SysPrefs->prefs['use_dimension'] = '0';

        // Invalidate cache
        CompanyPrefsService::invalidate();

        // Next call should get new value
        $result = CompanyPrefsService::getUseDimensions();
        $this->assertEquals(0, $result);
    }

    public function testObserverNotification(): void
    {
        $observerCalled = false;

        CompanyPrefsService::registerObserver(function() use (&$observerCalled) {
            $observerCalled = true;
        });

        CompanyPrefsService::invalidate();

        $this->assertTrue($observerCalled);
    }

    public function testNoSysPrefs(): void
    {
        // Simulate no SysPrefs
        global $SysPrefs;
        $SysPrefs = new class {
            public $prefs;
            public function refresh() {
                $this->prefs = ['use_dimension' => '0'];
            }
        };

        $result = CompanyPrefsService::getUseDimensions();
        $this->assertEquals(0, $result);
    }
}