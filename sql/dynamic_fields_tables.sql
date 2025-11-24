-- Dynamic Fields System (SuiteCRM-style)
-- Enables custom fields on any entity without schema changes

-- Custom Fields Table
-- Stores field definitions and metadata
CREATE TABLE IF NOT EXISTS `custom_fields` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_type` varchar(50) NOT NULL COMMENT 'Entity type (customers, suppliers, items, etc.)',
    `field_name` varchar(100) NOT NULL COMMENT 'Unique field identifier',
    `field_label` varchar(255) NOT NULL COMMENT 'Human-readable field label',
    `field_type` enum('text','textarea','number','decimal','date','datetime','boolean','select','multiselect','email','url','phone') NOT NULL DEFAULT 'text' COMMENT 'Field data type',
    `field_length` int(11) DEFAULT NULL COMMENT 'Maximum field length',
    `is_required` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Whether field is required',
    `default_value` text COMMENT 'Default field value',
    `validation_rules` text COMMENT 'JSON validation rules',
    `select_options` text COMMENT 'JSON options for select/multiselect fields',
    `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order in forms',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether field is active',
    `created_by` int(11) DEFAULT NULL COMMENT 'User who created the field',
    `created_at` datetime NOT NULL COMMENT 'Creation timestamp',
    `updated_at` datetime NOT NULL COMMENT 'Last update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_entity_field` (`entity_type`, `field_name`),
    KEY `idx_entity_type` (`entity_type`),
    KEY `idx_active` (`is_active`),
    KEY `idx_display_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom field definitions';

-- Custom Field Values Table (Name-Value pairs)
-- Stores actual field values for entities
CREATE TABLE IF NOT EXISTS `custom_field_values` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_type` varchar(50) NOT NULL COMMENT 'Entity type',
    `entity_id` int(11) NOT NULL COMMENT 'Entity primary key',
    `field_name` varchar(100) NOT NULL COMMENT 'Field name',
    `field_value` text COMMENT 'Field value (JSON for complex types)',
    `created_at` datetime NOT NULL COMMENT 'Creation timestamp',
    `updated_at` datetime NOT NULL COMMENT 'Last update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_entity_field_value` (`entity_type`, `entity_id`, `field_name`),
    KEY `idx_entity_type_id` (`entity_type`, `entity_id`),
    KEY `idx_field_name` (`field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom field values storage';

-- Field Groups Table
-- Organizes fields into logical groups/tabs
CREATE TABLE IF NOT EXISTS `custom_field_groups` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `entity_type` varchar(50) NOT NULL COMMENT 'Entity type',
    `group_name` varchar(100) NOT NULL COMMENT 'Group identifier',
    `group_label` varchar(255) NOT NULL COMMENT 'Human-readable group label',
    `display_order` int(11) NOT NULL DEFAULT 0 COMMENT 'Display order',
    `is_collapsible` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether group can be collapsed',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether group is active',
    `created_at` datetime NOT NULL COMMENT 'Creation timestamp',
    `updated_at` datetime NOT NULL COMMENT 'Last update timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_entity_group` (`entity_type`, `group_name`),
    KEY `idx_entity_type` (`entity_type`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom field groups';

-- Link custom fields to groups
ALTER TABLE `custom_fields`
ADD COLUMN `group_id` int(11) DEFAULT NULL COMMENT 'Field group ID',
ADD KEY `idx_group_id` (`group_id`),
ADD CONSTRAINT `fk_custom_fields_group` FOREIGN KEY (`group_id`) REFERENCES `custom_field_groups` (`id`) ON DELETE SET NULL;

-- Field Permissions Table
-- Controls field visibility and editability by user roles
CREATE TABLE IF NOT EXISTS `custom_field_permissions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `field_id` int(11) NOT NULL COMMENT 'Custom field ID',
    `role_id` int(11) NOT NULL COMMENT 'Security role ID',
    `can_view` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Can view field',
    `can_edit` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Can edit field',
    `created_at` datetime NOT NULL COMMENT 'Creation timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_field_role` (`field_id`, `role_id`),
    KEY `idx_field_id` (`field_id`),
    KEY `idx_role_id` (`role_id`),
    CONSTRAINT `fk_field_permissions_field` FOREIGN KEY (`field_id`) REFERENCES `custom_fields` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_field_permissions_role` FOREIGN KEY (`role_id`) REFERENCES `security_roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Custom field permissions by role';

-- Field Validation Rules Table
-- Stores predefined validation rules
CREATE TABLE IF NOT EXISTS `custom_field_validation_rules` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `rule_name` varchar(100) NOT NULL COMMENT 'Rule identifier',
    `rule_label` varchar(255) NOT NULL COMMENT 'Human-readable rule name',
    `rule_type` enum('required','min_length','max_length','pattern','range','email','url','phone','custom') NOT NULL COMMENT 'Validation rule type',
    `rule_config` text COMMENT 'JSON configuration for the rule',
    `error_message` varchar(500) COMMENT 'Custom error message',
    `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether rule is active',
    `created_at` datetime NOT NULL COMMENT 'Creation timestamp',
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_rule_name` (`rule_name`),
    KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Predefined validation rules';

-- Insert default validation rules
INSERT INTO `custom_field_validation_rules` (`rule_name`, `rule_label`, `rule_type`, `rule_config`, `error_message`, `created_at`) VALUES
('required', 'Required Field', 'required', '{}', 'This field is required', NOW()),
('email', 'Valid Email', 'email', '{}', 'Please enter a valid email address', NOW()),
('url', 'Valid URL', 'url', '{}', 'Please enter a valid URL', NOW()),
('phone', 'Valid Phone', 'phone', '{}', 'Please enter a valid phone number', NOW()),
('min_length_3', 'Minimum 3 Characters', 'min_length', '{"min": 3}', 'Must be at least 3 characters long', NOW()),
('max_length_255', 'Maximum 255 Characters', 'max_length', '{"max": 255}', 'Must not exceed 255 characters', NOW());