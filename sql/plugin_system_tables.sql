-- Plugin System Tables
-- These tables support the WordPress-style plugin system for FrontAccounting

-- Plugin Registry Table
-- Stores information about all registered plugins
CREATE TABLE IF NOT EXISTS `plugin_registry` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL COMMENT 'Unique plugin identifier',
    `version` varchar(20) NOT NULL COMMENT 'Plugin version',
    `description` text COMMENT 'Plugin description',
    `author` varchar(100) COMMENT 'Plugin author',
    `min_fa_version` varchar(20) COMMENT 'Minimum FA version required',
    `max_fa_version` varchar(20) COMMENT 'Maximum FA version supported',
    `dependencies` text COMMENT 'JSON array of plugin dependencies',
    `hooks` text COMMENT 'JSON array of registered hooks',
    `admin_menu_items` text COMMENT 'JSON array of admin menu items',
    `settings` text COMMENT 'JSON array of plugin settings',
    `installed` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether plugin is installed',
    `active` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether plugin is active',
    `installed_at` datetime DEFAULT NULL COMMENT 'Installation timestamp',
    `activated_at` datetime DEFAULT NULL COMMENT 'Activation timestamp',
    `deactivated_at` datetime DEFAULT NULL COMMENT 'Deactivation timestamp',
    `created_at` datetime NOT NULL COMMENT 'Record creation timestamp',
    `updated_at` datetime NOT NULL COMMENT 'Record update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_name` (`name`),
    KEY `idx_active` (`active`),
    KEY `idx_installed` (`installed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Plugin registry and metadata';

-- Active Plugins Table
-- Tracks which plugins are currently active
CREATE TABLE IF NOT EXISTS `active_plugins` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(100) NOT NULL COMMENT 'Plugin name',
    `activated_at` datetime NOT NULL COMMENT 'Activation timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_plugin` (`plugin_name`),
    KEY `idx_activated_at` (`activated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Currently active plugins';

-- Plugin Settings Table
-- Stores plugin-specific settings
CREATE TABLE IF NOT EXISTS `plugin_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(100) NOT NULL COMMENT 'Plugin name',
    `setting_key` varchar(100) NOT NULL COMMENT 'Setting key',
    `setting_value` text COMMENT 'Setting value (JSON)',
    `created_at` datetime NOT NULL COMMENT 'Record creation timestamp',
    `updated_at` datetime NOT NULL COMMENT 'Record update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_plugin_setting` (`plugin_name`, `setting_key`),
    KEY `idx_plugin_name` (`plugin_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Plugin-specific settings storage';

-- Plugin Logs Table
-- Logs plugin-related activities
CREATE TABLE IF NOT EXISTS `plugin_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `plugin_name` varchar(100) COMMENT 'Plugin name (NULL for system)',
    `level` enum('debug','info','warning','error') NOT NULL DEFAULT 'info' COMMENT 'Log level',
    `message` text NOT NULL COMMENT 'Log message',
    `context` text COMMENT 'JSON context data',
    `created_at` datetime NOT NULL COMMENT 'Log timestamp',
    PRIMARY KEY (`id`),
    KEY `idx_plugin_name` (`plugin_name`),
    KEY `idx_level` (`level`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Plugin activity logs';