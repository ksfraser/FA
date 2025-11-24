<?php
require_once 'vendor/autoload.php';

$vendorDir = dirname(__DIR__);  // vendor/composer -> vendor
$baseDir = dirname($vendorDir); // vendor -> project root
echo 'Vendor dir: ' . $vendorDir . PHP_EOL;
echo 'Base dir: ' . $baseDir . PHP_EOL;
echo 'Expected path: ' . $baseDir . '/temp_plugin_system/src/PluginSystem/Database/MockDatabaseAdapter.php' . PHP_EOL;
echo 'File exists: ' . (file_exists($baseDir . '/temp_plugin_system/src/PluginSystem/Database/MockDatabaseAdapter.php') ? 'yes' : 'no') . PHP_EOL;

echo 'Class exists: ' . (class_exists('Ksfraser\PluginSystem\Database\MockDatabaseAdapter') ? 'yes' : 'no') . PHP_EOL;