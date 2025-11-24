# FA Plugin System

A modular, dependency-injected plugin system for PHP applications, designed to be standalone and reusable across different projects.

## Features

- **Dependency Injection**: Clean architecture with injectable database and event dispatcher interfaces
- **Database Abstraction**: Works with any database through adapters (MySQL, PostgreSQL, SQLite, etc.)
- **Event-Driven**: PSR-14 compliant event system for plugin hooks and lifecycle events
- **Plugin Lifecycle Management**: Installation, activation, deactivation, and uninstallation
- **Dependency Resolution**: Automatic handling of plugin dependencies
- **Test-Friendly**: Comprehensive mocking support for unit testing
- **Type-Safe**: Full PHP 8.1+ type declarations and strict typing

## Architecture

The plugin system follows clean architecture principles with clear separation of concerns:

```
PluginManager (Core Logic)
├── PluginDatabaseInterface (Database Abstraction)
├── PluginEventDispatcherInterface (Event Abstraction)
├── PluginInterface (Plugin Contract)
└── Plugin Adapters (FA, Mock implementations)
```

## Installation

```bash
composer require fa/plugin-system
```

## Basic Usage

```php
use FA\Plugins\PluginManager;
use FA\Plugins\Database\FADatabaseAdapter;
use FA\Plugins\EventDispatcher\FAEventDispatcherAdapter;

// Initialize with your adapters
$db = new FADatabaseAdapter();
$events = new FAEventDispatcherAdapter();
$pluginManager = PluginManager::getInstance($db, $events);

// Load plugins from directory
$pluginManager->loadPluginsFromDirectory('/path/to/plugins');

// Activate a plugin
$pluginManager->activatePlugin('my-plugin');
```

## Creating a Plugin

```php
<?php
use FA\Plugins\BasePlugin;

class MyPlugin extends BasePlugin
{
    public function __construct()
    {
        $this->name = 'my-plugin';
        $this->version = '1.0.0';
        $this->description = 'My awesome plugin';
        $this->author = 'Your Name';
        $this->minFAVersion = '2.4.0';

        // Define hooks
        $this->hooks = [
            'customer.created' => [$this, 'onCustomerCreated'],
            'invoice.paid' => 'onInvoicePaid'
        ];

        // Define admin menu items
        $this->adminMenuItems = [
            'My Plugin' => 'my_plugin.php'
        ];

        // Define settings
        $this->settings = [
            'api_key' => [
                'type' => 'text',
                'label' => 'API Key',
                'default' => ''
            ]
        ];
    }

    protected function onActivate(): bool
    {
        // Plugin activation logic
        return true;
    }

    protected function onDeactivate(): bool
    {
        // Plugin deactivation logic
        return true;
    }

    public function onCustomerCreated($event)
    {
        // Handle customer creation
    }

    public function onInvoicePaid($event)
    {
        // Handle invoice payment
    }
}
```

## Database Adapters

### Built-in Adapters

- **FADatabaseAdapter**: For FrontAccounting applications
- **MockDatabaseAdapter**: For testing and development

### Creating Custom Adapters

```php
<?php
use FA\Plugins\Interfaces\PluginDatabaseInterface;

class MySQLAdapter implements PluginDatabaseInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query(string $sql, ?string $errorMsg = null)
    {
        return $this->pdo->query($sql);
    }

    public function fetchAssoc($result): ?array
    {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function escape(string $value): string
    {
        return $this->pdo->quote($value);
    }

    public function getTablePrefix(): string
    {
        return 'myapp_';
    }

    public function insertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}
```

## Event Dispatchers

### Built-in Dispatchers

- **FAEventDispatcherAdapter**: For FrontAccounting applications
- **NullEventDispatcher**: For testing or when events aren't needed

### Creating Custom Dispatchers

```php
<?php
use FA\Plugins\Interfaces\PluginEventDispatcherInterface;

class PSR14EventDispatcher implements PluginEventDispatcherInterface
{
    private Psr\EventDispatcher\EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function on(string $eventName, callable $listener): void
    {
        // Implementation depends on your event dispatcher
    }
}
```

## Testing

```php
use FA\Plugins\PluginManager;
use FA\Plugins\Database\MockDatabaseAdapter;
use FA\Plugins\EventDispatcher\NullEventDispatcher;

// Use mock adapters for testing
$db = new MockDatabaseAdapter();
$events = new NullEventDispatcher();
$pluginManager = PluginManager::getInstance($db, $events);

// Test your plugins
```

## Database Schema

The plugin system requires these tables:

```sql
-- Plugin registry
CREATE TABLE plugin_registry (
    name VARCHAR(255) PRIMARY KEY,
    version VARCHAR(50) NOT NULL,
    description TEXT,
    author VARCHAR(255),
    min_app_version VARCHAR(50),
    max_app_version VARCHAR(50),
    dependencies TEXT,
    hooks TEXT,
    admin_menu_items TEXT,
    settings TEXT,
    installed TINYINT DEFAULT 0,
    active TINYINT DEFAULT 0,
    created_at DATETIME,
    updated_at DATETIME,
    installed_at DATETIME,
    activated_at DATETIME,
    deactivated_at DATETIME
);

-- Active plugins
CREATE TABLE active_plugins (
    plugin_name VARCHAR(255) PRIMARY KEY,
    activated_at DATETIME
);
```

## Events

The plugin system dispatches these events:

- `PluginInstalledEvent`: When a plugin is installed
- `PluginActivatedEvent`: When a plugin is activated
- `PluginDeactivatedEvent`: When a plugin is deactivated
- `PluginUninstalledEvent`: When a plugin is uninstalled

## Requirements

- PHP 8.1+
- PSR-14 Event Dispatcher (optional, for event functionality)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

## License

This project is licensed under the GPL v3 License - see the LICENSE file for details.