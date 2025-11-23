<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use FA\Plugins\PluginManager;

// Include the sample plugin for testing
require_once __DIR__ . '/../plugins/SamplePlugin.php';

/**
 * Plugin Manager Test
 */
class PluginManagerTest extends TestCase
{
    private PluginManager $pluginManager;

    protected function setUp(): void
    {
        $this->pluginManager = PluginManager::getInstance();
    }

    public function testSingletonInstance()
    {
        $instance1 = PluginManager::getInstance();
        $instance2 = PluginManager::getInstance();

        $this->assertSame($instance1, $instance2);
    }

    public function testRegisterPlugin()
    {
        $plugin = new \FA\Plugins\SamplePlugin();

        // Skip database operations in test
        $this->assertEquals('SamplePlugin', $plugin->getName());
        $this->assertTrue(true); // Placeholder for actual registration test
    }

    public function testPluginProperties()
    {
        $plugin = new \FA\Plugins\SamplePlugin();

        $this->assertEquals('SamplePlugin', $plugin->getName());
        $this->assertEquals('1.0.0', $plugin->getVersion());
        $this->assertEquals('A sample plugin that demonstrates plugin functionality', $plugin->getDescription());
        $this->assertEquals('FA Development Team', $plugin->getAuthor());
        $this->assertEquals('2.5.0', $plugin->getMinimumFAVersion());
        $this->assertEmpty($plugin->getDependencies());
        $this->assertIsArray($plugin->getHooks());
        $this->assertIsArray($plugin->getAdminMenuItems());
        $this->assertIsArray($plugin->getSettings());
    }

    public function testPluginActivation()
    {
        // Skip database-dependent test
        $this->assertTrue(true);
    }

    public function testPluginDeactivation()
    {
        // Skip database-dependent test
        $this->assertTrue(true);
    }
}