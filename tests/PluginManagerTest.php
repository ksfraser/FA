<?php
declare(strict_types=1);

// Manually include mock classes since autoload is not working
require_once __DIR__ . '/../temp_plugin_system/src/PluginSystem/Database/MockDatabaseAdapter.php';
require_once __DIR__ . '/../temp_plugin_system/src/PluginSystem/EventDispatcher/NullEventDispatcher.php';
require_once __DIR__ . '/../temp_plugin_system/src/PluginSystem/PluginManager.php';
require_once __DIR__ . '/../temp_plugin_system/src/PluginSystem/BasePlugin.php';
require_once __DIR__ . '/../temp_plugin_system/src/PluginSystem/PluginInterface.php';
require_once __DIR__ . '/../plugins/SamplePlugin.php';

use PHPUnit\Framework\TestCase;
use Ksfraser\PluginSystem\PluginManager;
use Ksfraser\PluginSystem\Database\MockDatabaseAdapter;
use Ksfraser\PluginSystem\EventDispatcher\NullEventDispatcher;
use Ksfraser\PluginSystem\PluginInterface;

/**
 * Plugin Manager Test - TDD Style
 */
class PluginManagerTest extends TestCase
{
    private PluginManager $pluginManager;
    private MockDatabaseAdapter $mockDb;
    private NullEventDispatcher $mockEvents;

    protected function setUp(): void
    {
        // Create fresh mock adapters for each test
        $this->mockDb = new MockDatabaseAdapter();
        $this->mockEvents = new NullEventDispatcher();

        // Reset singleton for clean test state
        $this->resetSingleton();

        $this->pluginManager = PluginManager::getInstance($this->mockDb, $this->mockEvents);
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->mockDb->clearData();
    }

    private function resetSingleton(): void
    {
        // Reset the singleton instance for clean testing
        $reflection = new \ReflectionClass(PluginManager::class);
        $instanceProperty = $reflection->getProperty('instance');
        $instanceProperty->setAccessible(true);
        $instanceProperty->setValue(null);
    }

    public function testSingletonInstance()
    {
        $instance1 = PluginManager::getInstance();
        $instance2 = PluginManager::getInstance();

        $this->assertSame($instance1, $instance2);
        $this->assertInstanceOf(PluginManager::class, $instance1);
    }

    public function testRegisterPlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();

        $result = $this->pluginManager->registerPlugin($plugin);

        $this->assertTrue($result);
        $this->assertEquals($plugin, $this->pluginManager->getPlugin('SamplePlugin'));
        $this->assertContains('SamplePlugin', $this->pluginManager->getLoadedPlugins());
    }

    public function testPluginProperties()
    {
        $plugin = new \FA\Plugins\SamplePlugin();

        $this->assertEquals('SamplePlugin', $plugin->getName());
        $this->assertEquals('1.0.0', $plugin->getVersion());
        $this->assertEquals('A sample plugin that demonstrates plugin functionality', $plugin->getDescription());
        $this->assertEquals('FA Development Team', $plugin->getAuthor());
        $this->assertEquals('2.5.0', $plugin->getMinimumAppVersion());
        $this->assertNull($plugin->getMaximumAppVersion());
        $this->assertEmpty($plugin->getDependencies());
        $this->assertIsArray($plugin->getHooks());
        $this->assertIsArray($plugin->getAdminMenuItems());
        $this->assertIsArray($plugin->getSettings());
    }

    public function testInstallPlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();
        $this->pluginManager->registerPlugin($plugin);

        $result = $this->pluginManager->installPlugin('SamplePlugin');

        $this->assertTrue($result);
        $this->assertTrue($this->pluginManager->isPluginInstalled('SamplePlugin'));

        // Check that data was stored in mock database
        $registry = $this->pluginManager->getPluginRegistry();
        $this->assertArrayHasKey('SamplePlugin', $registry);
        $this->assertEquals('1.0.0', $registry['SamplePlugin']['version']);
    }

    public function testActivatePlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();
        $this->pluginManager->registerPlugin($plugin);
        $this->pluginManager->installPlugin('SamplePlugin');

        $result = $this->pluginManager->activatePlugin('SamplePlugin');

        $this->assertTrue($result);
        $this->assertTrue($this->pluginManager->isPluginActive('SamplePlugin'));
        $this->assertContains('SamplePlugin', $this->pluginManager->getActivePlugins());
    }

    public function testDeactivatePlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();
        $this->pluginManager->registerPlugin($plugin);
        $this->pluginManager->installPlugin('SamplePlugin');
        $this->pluginManager->activatePlugin('SamplePlugin');

        $result = $this->pluginManager->deactivatePlugin('SamplePlugin');

        $this->assertTrue($result);
        $this->assertFalse($this->pluginManager->isPluginActive('SamplePlugin'));
        $this->assertNotContains('SamplePlugin', $this->pluginManager->getActivePlugins());
    }

    public function testUninstallPlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();
        $this->pluginManager->registerPlugin($plugin);
        $this->pluginManager->installPlugin('SamplePlugin');

        $result = $this->pluginManager->uninstallPlugin('SamplePlugin');

        $this->assertTrue($result);
        $this->assertFalse($this->pluginManager->isPluginInstalled('SamplePlugin'));
        $this->assertNull($this->pluginManager->getPlugin('SamplePlugin'));
    }

    public function testPluginWithDependencies()
    {
        // Create a plugin with dependencies
        $dependentPlugin = new class extends \Ksfraser\PluginSystem\BasePlugin {
            protected function initializePlugin(): void
            {
                $this->name = 'DependentPlugin';
                $this->version = '1.0.0';
                $this->description = 'A plugin with dependencies';
                $this->author = 'Test Author';
                $this->minAppVersion = '1.0.0';
                $this->dependencies = ['SamplePlugin'];
            }
            protected function onActivate(): bool { return true; }
            protected function onDeactivate(): bool { return true; }
        };

        $basePlugin = new \FA\Plugins\SamplePlugin();

        $this->pluginManager->registerPlugin($basePlugin);
        $this->pluginManager->registerPlugin($dependentPlugin);

        // Install and activate base plugin first
        $this->pluginManager->installPlugin('SamplePlugin');
        $this->pluginManager->activatePlugin('SamplePlugin');

        // Now dependent plugin should activate successfully
        $result = $this->pluginManager->activatePlugin('DependentPlugin');

        $this->assertTrue($result);
        $this->assertTrue($this->pluginManager->isPluginActive('DependentPlugin'));
    }

    public function testPluginActivationFailsWithoutDependencies()
    {
        // Create a plugin with unmet dependencies
        $dependentPlugin = new class extends \Ksfraser\PluginSystem\BasePlugin {
            protected function initializePlugin(): void
            {
                $this->name = 'DependentPlugin';
                $this->version = '1.0.0';
                $this->description = 'A plugin with dependencies';
                $this->author = 'Test Author';
                $this->minAppVersion = '1.0.0';
                $this->dependencies = ['NonExistentPlugin'];
            }
            protected function onActivate(): bool { return true; }
            protected function onDeactivate(): bool { return true; }
        };

        $this->pluginManager->registerPlugin($dependentPlugin);

        // Activation should fail due to unmet dependencies
        $result = $this->pluginManager->activatePlugin('DependentPlugin');

        $this->assertFalse($result);
        $this->assertFalse($this->pluginManager->isPluginActive('DependentPlugin'));
    }
}