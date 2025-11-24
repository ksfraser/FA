<?php
require 'C:/Users/prote/FA/vendor/autoload.php';

try {
    $mockDb = new Ksfraser\PluginSystem\Database\MockDatabaseAdapter();
    echo "MockDatabaseAdapter loaded successfully\n";
} catch (Exception $e) {
    echo "Error loading MockDatabaseAdapter: " . $e->getMessage() . "\n";
}

try {
    $nullDispatcher = new Ksfraser\PluginSystem\EventDispatcher\NullEventDispatcher();
    echo "NullEventDispatcher loaded successfully\n";
} catch (Exception $e) {
    echo "Error loading NullEventDispatcher: " . $e->getMessage() . "\n";
}
?>