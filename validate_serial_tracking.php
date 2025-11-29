<?php
/**
 * Serial Tracking Module Validation Script
 *
 * Simple validation to ensure the serial tracking module works correctly
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "===========================================\n";
echo "Serial Tracking Module Validation\n";
echo "===========================================\n\n";

try {
    // Test module instantiation
    $module = new FA\Modules\SerialTrackingModule();

    echo "✓ Module instantiated successfully\n";
    echo "  Name: " . $module->getName() . "\n";
    echo "  Version: " . $module->getVersion() . "\n";
    echo "  Description: " . substr($module->getDescription(), 0, 50) . "...\n";
    echo "  Author: " . $module->getAuthor() . "\n";
    echo "  Min Version: " . $module->getMinimumAppVersion() . "\n";

    // Test dependencies
    $deps = $module->getDependencies();
    echo "  Dependencies: " . implode(', ', $deps) . "\n";

    // Test menu items
    $menuItems = $module->getMenuItems();
    echo "  Menu sections: " . count($menuItems) . "\n";

    // Test permissions
    $permissions = $module->getPermissions();
    echo "  Permissions: " . count($permissions) . "\n";

    echo "\n✓ All basic module functions working correctly\n";

    // Test database functions if available
    if (function_exists('is_serialised_item')) {
        echo "✓ Serial tracking database functions available\n";
    } else {
        echo "! Serial tracking database functions not loaded\n";
    }

    echo "\n===========================================\n";
    echo "Validation completed successfully!\n";
    echo "===========================================\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}