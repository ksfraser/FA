<?php
declare(strict_types=1);

namespace FA\Plugins;

use FA\Services\DatabaseService;
use FA\Services\EventManager;
use FA\Events\PluginActivatedEvent;
use FA\Events\PluginDeactivatedEvent;
use FA\Events\PluginInstalledEvent;
use FA\Events\PluginUninstalledEvent;

/**
 * Plugin Manager
 *
 * Manages plugin lifecycle, loading, activation, and dependencies
 */
class PluginManager
{
    private static ?PluginManager $instance = null;
    private array $loadedPlugins = [];
    private array $activePlugins = [];
    private array $pluginRegistry = [];

    /**
     * Get singleton instance
     */
    public static function getInstance(): PluginManager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        // Only load from database if functions are available
        if (function_exists('db_query')) {
            $this->loadPluginRegistry();
            $this->loadActivePlugins();
        }
    }

    /**
     * Load plugin registry from database
     */
    private function loadPluginRegistry(): void
    {
        $sql = "SELECT * FROM plugin_registry ORDER BY name";
        $result = db_query($sql);

        while ($row = db_fetch_assoc($result)) {
            $this->pluginRegistry[$row['name']] = $row;
        }
    }

    /**
     * Load active plugins from database
     */
    private function loadActivePlugins(): void
    {
        $sql = "SELECT plugin_name FROM active_plugins ORDER BY plugin_name";
        $result = db_query($sql);

        while ($row = db_fetch_assoc($result)) {
            $this->activePlugins[] = $row['plugin_name'];
        }
    }

    /**
     * Register a plugin in the system
     *
     * @param PluginInterface $plugin
     * @return bool
     */
    public function registerPlugin(PluginInterface $plugin): bool
    {
        $pluginName = $plugin->getName();

        // Check if plugin is already registered
        if (isset($this->pluginRegistry[$pluginName])) {
            return $this->updatePluginRegistration($plugin);
        }

        // Register new plugin
        $sql = "INSERT INTO plugin_registry (name, version, description, author, min_fa_version, max_fa_version, dependencies, hooks, admin_menu_items, settings, installed, active, created_at, updated_at) VALUES (" .
            db_escape($pluginName) . ", " .
            db_escape($plugin->getVersion()) . ", " .
            db_escape($plugin->getDescription()) . ", " .
            db_escape($plugin->getAuthor()) . ", " .
            db_escape($plugin->getMinimumFAVersion()) . ", " .
            db_escape($plugin->getMaximumFAVersion()) . ", " .
            db_escape(json_encode($plugin->getDependencies())) . ", " .
            db_escape(json_encode($plugin->getHooks())) . ", " .
            db_escape(json_encode($plugin->getAdminMenuItems())) . ", " .
            db_escape(json_encode($plugin->getSettings())) . ", " .
            "0, 0, " .
            db_escape(date('Y-m-d H:i:s')) . ", " .
            db_escape(date('Y-m-d H:i:s')) . ")";

        $result = db_query($sql);

        if ($result) {
            $data = [
                'name' => $pluginName,
                'version' => $plugin->getVersion(),
                'description' => $plugin->getDescription(),
                'author' => $plugin->getAuthor(),
                'min_fa_version' => $plugin->getMinimumFAVersion(),
                'max_fa_version' => $plugin->getMaximumFAVersion(),
                'dependencies' => json_encode($plugin->getDependencies()),
                'hooks' => json_encode($plugin->getHooks()),
                'admin_menu_items' => json_encode($plugin->getAdminMenuItems()),
                'settings' => json_encode($plugin->getSettings()),
                'installed' => 0,
                'active' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $this->pluginRegistry[$pluginName] = $data;
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        return false;
    }

    /**
     * Update existing plugin registration
     */
    private function updatePluginRegistration(PluginInterface $plugin): bool
    {
        $pluginName = $plugin->getName();

        $sql = "UPDATE plugin_registry SET " .
            "version = " . db_escape($plugin->getVersion()) . ", " .
            "description = " . db_escape($plugin->getDescription()) . ", " .
            "author = " . db_escape($plugin->getAuthor()) . ", " .
            "min_fa_version = " . db_escape($plugin->getMinimumFAVersion()) . ", " .
            "max_fa_version = " . db_escape($plugin->getMaximumFAVersion()) . ", " .
            "dependencies = " . db_escape(json_encode($plugin->getDependencies())) . ", " .
            "hooks = " . db_escape(json_encode($plugin->getHooks())) . ", " .
            "admin_menu_items = " . db_escape(json_encode($plugin->getAdminMenuItems())) . ", " .
            "settings = " . db_escape(json_encode($plugin->getSettings())) . ", " .
            "updated_at = " . db_escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . db_escape($pluginName);

        $result = db_query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['version'] = $plugin->getVersion();
            $this->pluginRegistry[$pluginName]['description'] = $plugin->getDescription();
            $this->pluginRegistry[$pluginName]['author'] = $plugin->getAuthor();
            $this->pluginRegistry[$pluginName]['min_fa_version'] = $plugin->getMinimumFAVersion();
            $this->pluginRegistry[$pluginName]['max_fa_version'] = $plugin->getMaximumFAVersion();
            $this->pluginRegistry[$pluginName]['dependencies'] = json_encode($plugin->getDependencies());
            $this->pluginRegistry[$pluginName]['hooks'] = json_encode($plugin->getHooks());
            $this->pluginRegistry[$pluginName]['admin_menu_items'] = json_encode($plugin->getAdminMenuItems());
            $this->pluginRegistry[$pluginName]['settings'] = json_encode($plugin->getSettings());
            $this->pluginRegistry[$pluginName]['updated_at'] = date('Y-m-d H:i:s');
            $this->loadedPlugins[$pluginName] = $plugin;
            return true;
        }

        return false;
    }

    /**
     * Install a plugin
     *
     * @param string $pluginName
     * @return bool
     */
    public function installPlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Check dependencies
        if (!$this->checkDependencies($plugin)) {
            return false;
        }

        // Run installation
        if (!$plugin->install()) {
            return false;
        }

        // Update registry
        $sql = "UPDATE plugin_registry SET " .
            "installed = 1, " .
            "installed_at = " . db_escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . db_escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . db_escape($pluginName);

        $result = db_query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['installed'] = 1;
            $this->pluginRegistry[$pluginName]['installed_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            EventManager::dispatchEvent(new PluginInstalledEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Activate a plugin
     *
     * @param string $pluginName
     * @return bool
     */
    public function activatePlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Check if plugin is installed
        if (!$this->pluginRegistry[$pluginName]['installed']) {
            return false;
        }

        // Check dependencies
        if (!$this->checkDependencies($plugin)) {
            return false;
        }

        // Run activation
        if (!$plugin->activate()) {
            return false;
        }

        // Register hooks
        $this->registerPluginHooks($plugin);

        // Add to active plugins
        if (!in_array($pluginName, $this->activePlugins)) {
            $this->activePlugins[] = $pluginName;

            // Update database
            $sql = "INSERT INTO active_plugins (plugin_name, activated_at) VALUES (" .
                db_escape($pluginName) . ", " .
                db_escape(date('Y-m-d H:i:s')) . ")";
            db_query($sql);
        }

        // Update registry
        $sql = "UPDATE plugin_registry SET " .
            "active = 1, " .
            "activated_at = " . db_escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . db_escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . db_escape($pluginName);

        $result = db_query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['active'] = 1;
            $this->pluginRegistry[$pluginName]['activated_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            EventManager::dispatchEvent(new PluginActivatedEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Deactivate a plugin
     *
     * @param string $pluginName
     * @return bool
     */
    public function deactivatePlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Run deactivation
        if (!$plugin->deactivate()) {
            return false;
        }

        // Unregister hooks
        $this->unregisterPluginHooks($plugin);

        // Remove from active plugins
        if (($key = array_search($pluginName, $this->activePlugins)) !== false) {
            unset($this->activePlugins[$key]);

            // Update database
            $sql = "DELETE FROM active_plugins WHERE plugin_name = " . db_escape($pluginName);
            db_query($sql);
        }

        // Update registry
        $sql = "UPDATE plugin_registry SET " .
            "active = 0, " .
            "deactivated_at = " . db_escape(date('Y-m-d H:i:s')) . ", " .
            "updated_at = " . db_escape(date('Y-m-d H:i:s')) . " " .
            "WHERE name = " . db_escape($pluginName);

        $result = db_query($sql);

        if ($result) {
            $this->pluginRegistry[$pluginName]['active'] = 0;
            $this->pluginRegistry[$pluginName]['deactivated_at'] = date('Y-m-d H:i:s');

            // Dispatch event
            EventManager::dispatchEvent(new PluginDeactivatedEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Uninstall a plugin
     *
     * @param string $pluginName
     * @return bool
     */
    public function uninstallPlugin(string $pluginName): bool
    {
        if (!isset($this->loadedPlugins[$pluginName])) {
            return false;
        }

        $plugin = $this->loadedPlugins[$pluginName];

        // Deactivate first if active
        if ($this->isPluginActive($pluginName)) {
            $this->deactivatePlugin($pluginName);
        }

        // Run uninstallation
        if (!$plugin->uninstall()) {
            return false;
        }

        // Remove from registry
        $sql = "DELETE FROM plugin_registry WHERE name = " . db_escape($pluginName);
        $result = db_query($sql);

        if ($result) {
            unset($this->pluginRegistry[$pluginName]);
            unset($this->loadedPlugins[$pluginName]);

            // Dispatch event
            EventManager::dispatchEvent(new PluginUninstalledEvent($pluginName, $plugin));

            return true;
        }

        return false;
    }

    /**
     * Check if plugin dependencies are satisfied
     */
    private function checkDependencies(PluginInterface $plugin): bool
    {
        $dependencies = $plugin->getDependencies();

        foreach ($dependencies as $dependency) {
            if (!$this->isPluginActive($dependency)) {
                error_log("Plugin {$plugin->getName()} requires dependency: {$dependency}");
                return false;
            }
        }

        return true;
    }

    /**
     * Register plugin hooks with the event system
     */
    private function registerPluginHooks(PluginInterface $plugin): void
    {
        $hooks = $plugin->getHooks();

        foreach ($hooks as $eventName => $handler) {
            if (is_callable($handler)) {
                EventManager::on($eventName, $handler);
            } elseif (is_array($handler) && count($handler) === 2) {
                // Handler specified as [class, method]
                $callable = [$plugin, $handler[1]];
                if (is_callable($callable)) {
                    EventManager::on($eventName, $callable);
                }
            }
        }
    }

    /**
     * Unregister plugin hooks from the event system
     */
    private function unregisterPluginHooks(PluginInterface $plugin): void
    {
        $hooks = $plugin->getHooks();

        foreach ($hooks as $eventName => $handler) {
            // Note: EventManager doesn't currently support removing specific listeners
            // This would need to be enhanced in the EventManager
            // For now, we'll rely on plugin deactivation to stop hook execution
        }
    }

    /**
     * Check if a plugin is active
     */
    public function isPluginActive(string $pluginName): bool
    {
        return in_array($pluginName, $this->activePlugins);
    }

    /**
     * Check if a plugin is installed
     */
    public function isPluginInstalled(string $pluginName): bool
    {
        return isset($this->pluginRegistry[$pluginName]) &&
               $this->pluginRegistry[$pluginName]['installed'];
    }

    /**
     * Get all loaded plugins
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }

    /**
     * Get all active plugins
     */
    public function getActivePlugins(): array
    {
        return $this->activePlugins;
    }

    /**
     * Get plugin registry
     */
    public function getPluginRegistry(): array
    {
        return $this->pluginRegistry;
    }

    /**
     * Get a specific plugin instance
     */
    public function getPlugin(string $pluginName): ?PluginInterface
    {
        return $this->loadedPlugins[$pluginName] ?? null;
    }

    /**
     * Load plugins from filesystem
     *
     * @param string $pluginsDir Directory containing plugin files
     */
    public function loadPluginsFromDirectory(string $pluginsDir): void
    {
        if (!is_dir($pluginsDir)) {
            return;
        }

        $pluginFiles = glob($pluginsDir . '/*.php');

        foreach ($pluginFiles as $pluginFile) {
            $this->loadPluginFromFile($pluginFile);
        }
    }

    /**
     * Load a single plugin from file
     */
    private function loadPluginFromFile(string $pluginFile): void
    {
        if (!file_exists($pluginFile)) {
            return;
        }

        try {
            // Include the plugin file
            include_once $pluginFile;

            // Try to find the plugin class (assuming it matches filename)
            $className = basename($pluginFile, '.php');
            $fullClassName = "FA\\Plugins\\{$className}";

            if (class_exists($fullClassName)) {
                $plugin = new $fullClassName();

                if ($plugin instanceof PluginInterface) {
                    $this->registerPlugin($plugin);
                }
            }
        } catch (\Exception $e) {
            error_log("Failed to load plugin from {$pluginFile}: " . $e->getMessage());
        }
    }
}