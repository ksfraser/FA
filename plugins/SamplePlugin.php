<?php
declare(strict_types=1);

namespace FA\Plugins;

use FA\Events\DatabasePostWriteEvent;

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
        $this->minFAVersion = '2.5.0';
        $this->maxFAVersion = null;
        $this->dependencies = [];

        // Register hooks
        $this->registerHook('FA\Events\DatabasePostWriteEvent', [$this, 'onDatabasePostWrite']);

        // Add admin menu item
        $this->addAdminMenuItem('Sample Plugin', 'admin/sample_plugin.php');

        // Add settings
        $this->addSetting('log_level', 'Log Level', 'select', 'info', 'Logging verbosity level');
        $this->addSetting('enable_notifications', 'Enable Notifications', 'checkbox', true, 'Send notifications for important events');
    }

    /**
     * Handle database post-write events
     */
    public function onDatabasePostWrite(DatabasePostWriteEvent $event): void
    {
        $transactionType = $event->getTransactionType();

        $this->log("Database write operation completed for transaction type: {$transactionType}", 'info', [
            'transaction_type' => $transactionType,
            'timestamp' => date('Y-m-d H:i:s')
        ]);

        // Example: Send notification if enabled
        if ($this->getSetting('enable_notifications')) {
            // In a real plugin, you might send an email or other notification
            $this->log("Notification would be sent for transaction type: {$transactionType}", 'info');
        }
    }

    /**
     * Called when plugin is activated
     */
    protected function onActivate(): bool
    {
        $this->log("SamplePlugin activated", 'info');
        return true;
    }

    /**
     * Called when plugin is deactivated
     */
    protected function onDeactivate(): bool
    {
        $this->log("SamplePlugin deactivated", 'info');
        return true;
    }

    /**
     * Called when plugin is installed
     */
    protected function onInstall(): bool
    {
        $this->log("SamplePlugin installed", 'info');
        return true;
    }

    /**
     * Called when plugin is uninstalled
     */
    protected function onUninstall(): bool
    {
        $this->log("SamplePlugin uninstalled", 'info');
        return true;
    }

    /**
     * Called when plugin is upgraded
     */
    protected function onUpgrade(string $oldVersion, string $newVersion): bool
    {
        $this->log("SamplePlugin upgraded from {$oldVersion} to {$newVersion}", 'info');
        return true;
    }
}