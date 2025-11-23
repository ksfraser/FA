-- Event System and Workflow Tables
-- Part of Phase 2: Event System implementation

-- Workflows table - defines workflow templates
CREATE TABLE IF NOT EXISTS `workflows` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text,
    `event_trigger` varchar(255) NOT NULL COMMENT 'Event class that triggers this workflow',
    `conditions` text COMMENT 'JSON conditions for workflow execution',
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `event_trigger` (`event_trigger`),
    KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Workflow definitions';

-- Workflow steps table - defines individual steps in a workflow
CREATE TABLE IF NOT EXISTS `workflow_steps` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `workflow_id` int(11) NOT NULL,
    `step_order` int(11) NOT NULL,
    `step_type` enum('action','condition','delay','notification') NOT NULL,
    `step_name` varchar(255) NOT NULL,
    `configuration` text NOT NULL COMMENT 'JSON configuration for the step',
    `on_success_step` int(11) DEFAULT NULL,
    `on_failure_step` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `workflow_id` (`workflow_id`),
    KEY `step_order` (`step_order`),
    CONSTRAINT `fk_workflow_steps_workflow` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Workflow step definitions';

-- Workflow executions table - tracks running workflow instances
CREATE TABLE IF NOT EXISTS `workflow_executions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `workflow_id` int(11) NOT NULL,
    `trigger_event` text NOT NULL COMMENT 'Serialized event that triggered the workflow',
    `status` enum('running','completed','failed','cancelled') NOT NULL DEFAULT 'running',
    `current_step_id` int(11) DEFAULT NULL,
    `context_data` text COMMENT 'JSON context data for the workflow execution',
    `started_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `completed_at` timestamp NULL DEFAULT NULL,
    `error_message` text,
    PRIMARY KEY (`id`),
    KEY `workflow_id` (`workflow_id`),
    KEY `status` (`status`),
    KEY `started_at` (`started_at`),
    CONSTRAINT `fk_workflow_executions_workflow` FOREIGN KEY (`workflow_id`) REFERENCES `workflows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Workflow execution instances';

-- Workflow step executions table - tracks execution of individual steps
CREATE TABLE IF NOT EXISTS `workflow_step_executions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `workflow_execution_id` int(11) NOT NULL,
    `step_id` int(11) NOT NULL,
    `status` enum('pending','running','completed','failed','skipped') NOT NULL DEFAULT 'pending',
    `input_data` text COMMENT 'JSON input data for the step',
    `output_data` text COMMENT 'JSON output data from the step',
    `error_message` text,
    `started_at` timestamp NULL DEFAULT NULL,
    `completed_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `workflow_execution_id` (`workflow_execution_id`),
    KEY `step_id` (`step_id`),
    KEY `status` (`status`),
    CONSTRAINT `fk_step_executions_workflow_execution` FOREIGN KEY (`workflow_execution_id`) REFERENCES `workflow_executions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_step_executions_step` FOREIGN KEY (`step_id`) REFERENCES `workflow_steps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Workflow step execution tracking';

-- Event log table - audit trail of all dispatched events
CREATE TABLE IF NOT EXISTS `event_log` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `event_name` varchar(255) NOT NULL,
    `event_data` text COMMENT 'Serialized event data',
    `dispatched_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `listener_count` int(11) NOT NULL DEFAULT 0,
    `processing_time_ms` int(11) DEFAULT NULL,
    `user_id` int(11) DEFAULT NULL,
    `ip_address` varchar(45) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `event_name` (`event_name`),
    KEY `dispatched_at` (`dispatched_at`),
    KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Event dispatch audit log';