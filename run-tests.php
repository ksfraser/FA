#!/usr/bin/env php
<?php
/**
 * Test Runner Script
 * 
 * Runs all PHPUnit tests and generates coverage report
 */

// Check if composer is available
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "Error: Composer dependencies not installed.\n";
    echo "Please run: composer install\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

echo "===========================================\n";
echo "FrontAccounting Test Suite Runner\n";
echo "===========================================\n\n";

// Check if PHPUnit is available
$phpunitPath = __DIR__ . '/vendor/bin/phpunit';
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    $phpunitPath .= '.bat';
}

if (!file_exists($phpunitPath)) {
    echo "Error: PHPUnit not found.\n";
    echo "Please run: composer install\n";
    exit(1);
}

echo "Running PHPUnit tests...\n\n";

// Run PHPUnit
passthru("\"$phpunitPath\" --colors=always", $returnCode);

echo "\n===========================================\n";
echo "Test suite completed with exit code: $returnCode\n";
echo "===========================================\n";

exit($returnCode);
