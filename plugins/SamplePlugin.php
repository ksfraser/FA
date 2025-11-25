<?php
declare(strict_types=1);

namespace FA\Plugins;

use Ksfraser\PluginSystem\BasePlugin;

/**
 * Sample Plugin
 *
 * Demonstrates how to create a plugin for FrontAccounting
 * This plugin logs all database write operations
 */
class SamplePlugin extends BasePlugin
{
    /**
     * Initialize plugin properties
     */
    protected function initializePlugin(): void
    {
        $this->name = 'SamplePlugin';
        $this->version = '1.0.0';
        $this->description = 'A sample plugin that demonstrates plugin functionality';
        $this->author = 'FA Development Team';
        $this->minAppVersion = '2.5.0';
        $this->maxAppVersion = null;
        $this->dependencies = [];

        // Register hooks
        $this->hooks = [
            'database.post_write' => [$this, 'onDatabasePostWrite']
        ];

        // Admin menu items
        $this->adminMenuItems = [
            'Sample Plugin' => 'sample_plugin.php'
        ];

        // Settings
        $this->settings = [
            'enabled' => [
                'type' => 'checkbox',
                'label' => 'Enable Plugin',
                'default' => true
            ]
        ];
    }

    /**
     * Handle database post-write events
     */
    public function onDatabasePostWrite($event): void
    {
        // Log the database write operation
        error_log("SamplePlugin: Database write operation detected");
    }

    /**
     * Called when plugin is activated
     */
    protected function onActivate(): bool
    {
        error_log("SamplePlugin: Plugin activated");
        return true;
    }

    /**
     * Called when plugin is deactivated
     */
    protected function onDeactivate(): bool
    {
        error_log("SamplePlugin: Plugin deactivated");
        return true;
    }
}