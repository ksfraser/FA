<?php
declare(strict_types=1);

namespace FA\Modules;

/**
 * Module Interface
 *
 * Defines the contract that all modules must implement
 */
interface ModuleInterface
{
    /**
     * Get the module name
     */
    public function getName(): string;

    /**
     * Get the module version
     */
    public function getVersion(): string;

    /**
     * Get the module description
     */
    public function getDescription(): string;

    /**
     * Get the module author
     */
    public function getAuthor(): string;

    /**
     * Get minimum application version required
     */
    public function getMinimumAppVersion(): string;

    /**
     * Get maximum application version supported (optional)
     */
    public function getMaximumAppVersion(): ?string;

    /**
     * Get module dependencies
     *
     * @return array Array of module names this module depends on
     */
    public function getDependencies(): array;

    /**
     * Get menu items this module adds
     *
     * @return array Array of menu items
     */
    public function getMenuItems(): array;

    /**
     * Get permissions this module requires
     *
     * @return array Array of permission definitions
     */
    public function getPermissions(): array;

    /**
     * Activate the module
     *
     * @return bool True on success, false on failure
     */
    public function activate(): bool;

    /**
     * Deactivate the module
     *
     * @return bool True on success, false on failure
     */
    public function deactivate(): bool;

    /**
     * Install the module (run once during initial installation)
     *
     * @return bool True on success, false on failure
     */
    public function install(): bool;

    /**
     * Uninstall the module (run once during removal)
     *
     * @return bool True on success, false on failure
     */
    public function uninstall(): bool;

    /**
     * Upgrade the module from old version to new version
     *
     * @param string $oldVersion
     * @param string $newVersion
     * @return bool True on success, false on failure
     */
    public function upgrade(string $oldVersion, string $newVersion): bool;
}