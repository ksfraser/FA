<?php
declare(strict_types=1);

namespace FA\Modules;

/**
 * Module Manager
 *
 * Manages module lifecycle, loading, activation, and dependencies
 */
class ModuleManager
{
    private static ?ModuleManager $instance = null;
    private array $loadedModules = [];
    private array $activeModules = [];
    private array $moduleRegistry = [];

    /**
     * Get singleton instance
     */
    public static function getInstance(): ModuleManager
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
        // Load modules from database or config
        $this->loadModuleRegistry();
        $this->loadActiveModules();
    }

    /**
     * Load module registry
     */
    private function loadModuleRegistry(): void
    {
        // For now, load from config or hardcoded
        // In full implementation, load from database
    }

    /**
     * Load active modules
     */
    private function loadActiveModules(): void
    {
        // Load active modules
    }

    /**
     * Register a module
     */
    public function registerModule(ModuleInterface $module): bool
    {
        $moduleName = $module->getName();

        if (isset($this->moduleRegistry[$moduleName])) {
            return false; // Already registered
        }

        $this->moduleRegistry[$moduleName] = [
            'name' => $moduleName,
            'version' => $module->getVersion(),
            'description' => $module->getDescription(),
            'author' => $module->getAuthor(),
            'min_app_version' => $module->getMinimumAppVersion(),
            'max_app_version' => $module->getMaximumAppVersion(),
            'dependencies' => $module->getDependencies(),
            'menu_items' => $module->getMenuItems(),
            'permissions' => $module->getPermissions(),
            'installed' => false,
            'active' => false,
        ];

        $this->loadedModules[$moduleName] = $module;
        return true;
    }

    /**
     * Install a module
     */
    public function installModule(string $moduleName): bool
    {
        if (!isset($this->loadedModules[$moduleName])) {
            return false;
        }

        $module = $this->loadedModules[$moduleName];

        // Check dependencies
        if (!$this->checkDependencies($module)) {
            return false;
        }

        // Run installation
        if (!$module->install()) {
            return false;
        }

        $this->moduleRegistry[$moduleName]['installed'] = true;
        return true;
    }

    /**
     * Activate a module
     */
    public function activateModule(string $moduleName): bool
    {
        if (!isset($this->loadedModules[$moduleName])) {
            return false;
        }

        $module = $this->loadedModules[$moduleName];

        // Check if installed
        if (!$this->moduleRegistry[$moduleName]['installed']) {
            return false;
        }

        // Check dependencies
        if (!$this->checkDependencies($module)) {
            return false;
        }

        // Run activation
        if (!$module->activate()) {
            return false;
        }

        $this->activeModules[] = $moduleName;
        $this->moduleRegistry[$moduleName]['active'] = true;
        return true;
    }

    /**
     * Deactivate a module
     */
    public function deactivateModule(string $moduleName): bool
    {
        if (!isset($this->loadedModules[$moduleName])) {
            return false;
        }

        $module = $this->loadedModules[$moduleName];

        // Run deactivation
        if (!$module->deactivate()) {
            return false;
        }

        if (($key = array_search($moduleName, $this->activeModules)) !== false) {
            unset($this->activeModules[$key]);
        }

        $this->moduleRegistry[$moduleName]['active'] = false;
        return true;
    }

    /**
     * Check dependencies
     */
    private function checkDependencies(ModuleInterface $module): bool
    {
        $dependencies = $module->getDependencies();

        foreach ($dependencies as $dependency) {
            if (!$this->isModuleActive($dependency)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if module is active
     */
    public function isModuleActive(string $moduleName): bool
    {
        return in_array($moduleName, $this->activeModules);
    }

    /**
     * Get active modules
     */
    public function getActiveModules(): array
    {
        return $this->activeModules;
    }

    /**
     * Get loaded modules
     */
    public function getLoadedModules(): array
    {
        return array_keys($this->loadedModules);
    }
}