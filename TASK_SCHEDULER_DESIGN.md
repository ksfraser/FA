# Task Scheduler & Automation System - Architecture Design

## Overview

A pluggable, event-driven task scheduling and automation system that integrates seamlessly with FA's modular architecture. Supports time-based, event-based, and conditional task execution with granular access control.

## Core Principles

1. **Pluggable**: Any module can register schedulable tasks without modifying core scheduler
2. **DI-Based**: Dependency injection for loose coupling and testability
3. **SRP**: Single Responsibility - scheduler coordinates, tasks execute
4. **Event-Driven**: Integrates with existing PSR-14 event system
5. **Secure**: Granular access control tied to module permissions
6. **Extensible**: Easy to add new task types and trigger mechanisms

## Architecture

### Core Components

```
┌──────────────────────────────────────────────────────────────┐
│                    TaskScheduler (Core)                       │
│  - Task registration & discovery                              │
│  - Execution orchestration                                    │
│  - Queue management                                           │
│  - Retry & error handling                                     │
└──────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────┐
│              TaskProviderRegistry (DI Container)              │
│  - Module task provider registration                          │
│  - Lazy loading of task implementations                       │
│  - Dependency resolution                                      │
└──────────────────────────────────────────────────────────────┘
                              ↓
        ┌─────────────────────┴────────────────────┬───────────┐
        ↓                     ↓                     ↓           ↓
┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐ │
│ ReportsProvider │  │  CRMProvider    │  │  TodoProvider   │ ...
│ - ReportTask    │  │ - ReminderTask  │  │ - DeadlineTask  │
│ - EmailTask     │  │ - FollowUpTask  │  │ - GoalTask      │
└─────────────────┘  └─────────────────┘  └─────────────────┘
```

## Implementation

### 1. Core Interfaces

```php
<?php
declare(strict_types=1);

namespace FA\Scheduler;

/**
 * Represents a schedulable task
 * All tasks must implement this interface
 */
interface SchedulableTaskInterface
{
    /**
     * Execute the task
     * @return TaskResult Execution result with status and data
     */
    public function execute(): TaskResult;
    
    /**
     * Check if task can be executed (dependencies, conditions met)
     */
    public function canExecute(): bool;
    
    /**
     * Get task metadata for scheduling
     */
    public function getMetadata(): TaskMetadata;
    
    /**
     * Called on successful execution
     */
    public function onSuccess(TaskResult $result): void;
    
    /**
     * Called on failed execution
     */
    public function onFailure(\Exception $e): void;
    
    /**
     * Get required permission to execute this task
     */
    public function getRequiredPermission(): string;
}

/**
 * Task provider - registers tasks for a module
 * Each module implements this to provide schedulable tasks
 */
interface TaskProviderInterface
{
    /**
     * Get module identifier (e.g., 'reports', 'crm', 'marketing')
     */
    public function getModuleId(): string;
    
    /**
     * Get base permission required for this module's tasks
     * (e.g., 'SA_REPORTS', 'SA_CRM', 'SA_MARKETING')
     */
    public function getBasePermission(): string;
    
    /**
     * Register available task types with the scheduler
     * @return array<string, class-string<SchedulableTaskInterface>>
     */
    public function getTaskTypes(): array;
    
    /**
     * Create task instance from schedule data
     */
    public function createTask(string $taskType, array $config): SchedulableTaskInterface;
    
    /**
     * Get UI components for task configuration
     * Returns array of form fields for scheduling this task type
     */
    public function getConfigurationUI(string $taskType): array;
    
    /**
     * Validate task configuration before scheduling
     */
    public function validateConfiguration(string $taskType, array $config): ValidationResult;
}

/**
 * Task metadata for scheduling
 */
class TaskMetadata
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $description,
        public readonly string $module,
        public readonly TaskScheduleType $scheduleType,
        public readonly ?string $cronExpression = null,
        public readonly ?string $eventName = null,
        public readonly ?string $condition = null,
        public readonly int $priority = Priority::NORMAL,
        public readonly int $retryCount = 3,
        public readonly int $timeout = 300
    ) {}
}

/**
 * Task execution result
 */
class TaskResult
{
    public function __construct(
        public readonly TaskStatus $status,
        public readonly ?string $message = null,
        public readonly ?array $data = null,
        public readonly ?int $nextRunTime = null
    ) {}
    
    public static function success(?string $message = null, ?array $data = null): self
    {
        return new self(TaskStatus::SUCCESS, $message, $data);
    }
    
    public static function failure(string $message, ?array $data = null): self
    {
        return new self(TaskStatus::FAILED, $message, $data);
    }
    
    public static function pending(?int $nextRun = null): self
    {
        return new self(TaskStatus::PENDING, null, null, $nextRun);
    }
}

enum TaskStatus: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}

enum TaskScheduleType: string
{
    case CRON = 'cron';          // Traditional cron expression
    case ONE_TIME = 'one_time';  // Run once at specific time
    case RECURRING = 'recurring'; // Simple recurring (daily, weekly, etc)
    case EVENT = 'event';        // Triggered by event
    case CONDITIONAL = 'conditional'; // When condition is met
}

enum Priority: int
{
    case HIGH = 1;
    case NORMAL = 5;
    case LOW = 10;
}
```

### 2. Task Provider Registry

```php
<?php
declare(strict_types=1);

namespace FA\Scheduler;

use Psr\Container\ContainerInterface;

/**
 * Registry for task providers from all modules
 * Uses DI container for lazy loading
 */
class TaskProviderRegistry
{
    /** @var array<string, TaskProviderInterface> */
    private array $providers = [];
    
    public function __construct(
        private ContainerInterface $container
    ) {}
    
    /**
     * Register a task provider (usually from module bootstrap)
     */
    public function register(string $moduleId, string $providerClass): void
    {
        if (!is_subclass_of($providerClass, TaskProviderInterface::class)) {
            throw new \InvalidArgumentException(
                "Provider must implement TaskProviderInterface"
            );
        }
        
        $this->providers[$moduleId] = $providerClass;
    }
    
    /**
     * Get provider for a module (lazy loaded)
     */
    public function getProvider(string $moduleId): TaskProviderInterface
    {
        if (!isset($this->providers[$moduleId])) {
            throw new \RuntimeException("No provider registered for module: $moduleId");
        }
        
        // If not yet instantiated, load from container
        if (is_string($this->providers[$moduleId])) {
            $this->providers[$moduleId] = $this->container->get(
                $this->providers[$moduleId]
            );
        }
        
        return $this->providers[$moduleId];
    }
    
    /**
     * Get all registered providers
     * @return array<string, TaskProviderInterface>
     */
    public function getAll(): array
    {
        // Lazy load all providers
        foreach ($this->providers as $moduleId => $provider) {
            if (is_string($provider)) {
                $this->getProvider($moduleId);
            }
        }
        
        return $this->providers;
    }
    
    /**
     * Get all available task types across all modules
     * @return array<string, array{module: string, class: class-string}>
     */
    public function getAllTaskTypes(): array
    {
        $allTypes = [];
        
        foreach ($this->getAll() as $moduleId => $provider) {
            foreach ($provider->getTaskTypes() as $taskType => $taskClass) {
                $allTypes["$moduleId.$taskType"] = [
                    'module' => $moduleId,
                    'class' => $taskClass
                ];
            }
        }
        
        return $allTypes;
    }
}
```

### 3. Core Task Scheduler

```php
<?php
declare(strict_types=1);

namespace FA\Scheduler;

use FA\Database\DBALInterface;
use FA\Events\EventDispatcher;
use Psr\Log\LoggerInterface;

/**
 * Core task scheduler
 * Coordinates execution of scheduled tasks
 */
class TaskScheduler
{
    public function __construct(
        private DBALInterface $db,
        private TaskProviderRegistry $registry,
        private EventDispatcher $events,
        private AccessControl $accessControl,
        private LoggerInterface $logger
    ) {}
    
    /**
     * Schedule a task
     */
    public function schedule(
        string $moduleId,
        string $taskType,
        array $config,
        TaskMetadata $metadata,
        int $userId
    ): int {
        // Get provider
        $provider = $this->registry->getProvider($moduleId);
        
        // Check permissions
        $requiredPermission = $provider->getBasePermission();
        if (!$this->accessControl->hasPermission($userId, $requiredPermission)) {
            throw new PermissionDeniedException(
                "User lacks permission: $requiredPermission"
            );
        }
        
        // Validate configuration
        $validation = $provider->validateConfiguration($taskType, $config);
        if (!$validation->isValid()) {
            throw new ValidationException($validation->getErrors());
        }
        
        // Create task instance to verify it's valid
        $task = $provider->createTask($taskType, $config);
        
        // Check task-specific permission
        $taskPermission = $task->getRequiredPermission();
        if ($taskPermission && !$this->accessControl->hasPermission($userId, $taskPermission)) {
            throw new PermissionDeniedException(
                "User lacks permission: $taskPermission"
            );
        }
        
        // Insert into schedule table
        $scheduleId = $this->db->insert('scheduled_tasks', [
            'module_id' => $moduleId,
            'task_type' => $taskType,
            'config' => json_encode($config),
            'metadata' => json_encode($metadata),
            'user_id' => $userId,
            'status' => TaskStatus::PENDING->value,
            'created_at' => date('Y-m-d H:i:s'),
            'next_run' => $this->calculateNextRun($metadata),
            'priority' => $metadata->priority
        ]);
        
        // Dispatch event
        $this->events->dispatch(new TaskScheduledEvent(
            $scheduleId,
            $moduleId,
            $taskType,
            $userId
        ));
        
        $this->logger->info("Task scheduled", [
            'schedule_id' => $scheduleId,
            'module' => $moduleId,
            'task' => $taskType,
            'user' => $userId
        ]);
        
        return $scheduleId;
    }
    
    /**
     * Execute due tasks (called by cron)
     */
    public function executeDueTasks(): array
    {
        $results = [];
        
        // Get tasks due for execution
        $tasks = $this->db->fetchAll(
            "SELECT * FROM scheduled_tasks 
             WHERE status = ? 
             AND (next_run IS NULL OR next_run <= ?)
             AND enabled = 1
             ORDER BY priority ASC, created_at ASC
             LIMIT 100",
            [TaskStatus::PENDING->value, date('Y-m-d H:i:s')]
        );
        
        foreach ($tasks as $taskData) {
            $results[] = $this->executeTask((int)$taskData['id']);
        }
        
        return $results;
    }
    
    /**
     * Execute a specific task
     */
    public function executeTask(int $scheduleId): TaskResult
    {
        // Get task data
        $taskData = $this->db->fetchOne(
            "SELECT * FROM scheduled_tasks WHERE id = ?",
            [$scheduleId]
        );
        
        if (!$taskData) {
            throw new \RuntimeException("Task not found: $scheduleId");
        }
        
        // Mark as running
        $this->db->update('scheduled_tasks', 
            ['status' => TaskStatus::RUNNING->value],
            ['id' => $scheduleId]
        );
        
        try {
            // Get provider and create task
            $provider = $this->registry->getProvider($taskData['module_id']);
            $config = json_decode($taskData['config'], true);
            $task = $provider->createTask($taskData['task_type'], $config);
            
            // Check if task can execute
            if (!$task->canExecute()) {
                return $this->markTaskPending($scheduleId);
            }
            
            // Dispatch before event
            $this->events->dispatch(new TaskExecutingEvent($scheduleId, $task));
            
            // Execute task
            $result = $task->execute();
            
            // Handle result
            if ($result->status === TaskStatus::SUCCESS) {
                $task->onSuccess($result);
                $this->markTaskSuccess($scheduleId, $result);
            } elseif ($result->status === TaskStatus::FAILED) {
                $this->markTaskFailed($scheduleId, $result);
            } else {
                $this->markTaskPending($scheduleId, $result->nextRunTime);
            }
            
            // Dispatch after event
            $this->events->dispatch(new TaskExecutedEvent($scheduleId, $result));
            
            return $result;
            
        } catch (\Exception $e) {
            // Handle failure
            $result = TaskResult::failure($e->getMessage());
            $task->onFailure($e);
            $this->markTaskFailed($scheduleId, $result, $e);
            
            $this->logger->error("Task execution failed", [
                'schedule_id' => $scheduleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $result;
        }
    }
    
    /**
     * Cancel a scheduled task
     */
    public function cancel(int $scheduleId, int $userId): void
    {
        // Check permission (can cancel if user scheduled it OR user is admin)
        if (!$this->accessControl->canManageTask($userId, $scheduleId)) {
            throw new PermissionDeniedException("Cannot cancel this task");
        }
        
        $this->db->update('scheduled_tasks',
            ['status' => TaskStatus::CANCELLED->value],
            ['id' => $scheduleId]
        );
        
        $this->events->dispatch(new TaskCancelledEvent($scheduleId, $userId));
    }
    
    private function calculateNextRun(TaskMetadata $metadata): ?string
    {
        // Calculate next run based on schedule type
        // ... implementation
    }
    
    private function markTaskSuccess(int $scheduleId, TaskResult $result): void
    {
        // ... update database
    }
    
    private function markTaskFailed(int $scheduleId, TaskResult $result, ?\Exception $e = null): void
    {
        // ... handle retries, update database
    }
    
    private function markTaskPending(int $scheduleId, ?int $nextRun = null): TaskResult
    {
        // ... update next run time
    }
}
```

### 4. Access Control

```php
<?php
declare(strict_types=1);

namespace FA\Scheduler;

/**
 * Access control for scheduled tasks
 */
class AccessControl
{
    public function __construct(
        private DBALInterface $db
    ) {}
    
    /**
     * Check if user has permission for an action
     * 
     * Rules:
     * 1. User must have SA_SCHEDULER permission to schedule anything
     * 2. User must have module permission (e.g., SA_REPORTS for report tasks)
     * 3. User must have specific task permission (if required)
     * 4. Users can manage their own scheduled tasks
     * 5. Users with SA_SCHEDULER_ADMIN can manage all tasks across modules
     */
    public function hasPermission(int $userId, string $permission): bool
    {
        // Check base scheduler permission
        if (!$this->userHas($userId, 'SA_SCHEDULER')) {
            return false;
        }
        
        // Check specific permission
        return $this->userHas($userId, $permission);
    }
    
    /**
     * Check if user can manage (view/edit/cancel) a scheduled task
     */
    public function canManageTask(int $userId, int $scheduleId): bool
    {
        // Admin can manage all tasks
        if ($this->userHas($userId, 'SA_SCHEDULER_ADMIN')) {
            return true;
        }
        
        // Get task data
        $task = $this->db->fetchOne(
            "SELECT user_id, module_id FROM scheduled_tasks WHERE id = ?",
            [$scheduleId]
        );
        
        if (!$task) {
            return false;
        }
        
        // User can manage their own tasks
        if ($task['user_id'] == $userId) {
            return true;
        }
        
        // User can manage tasks in modules they have admin access to
        $modulePermission = $this->getModuleAdminPermission($task['module_id']);
        return $this->userHas($userId, $modulePermission);
    }
    
    /**
     * Get tasks user can view
     */
    public function getViewableTasksForUser(int $userId): array
    {
        if ($this->userHas($userId, 'SA_SCHEDULER_ADMIN')) {
            // Admin sees all
            return $this->db->fetchAll(
                "SELECT * FROM scheduled_tasks WHERE enabled = 1"
            );
        }
        
        // User sees their own tasks + tasks for modules they have access to
        $modules = $this->getUserModuleAccess($userId);
        
        return $this->db->fetchAll(
            "SELECT * FROM scheduled_tasks 
             WHERE enabled = 1 
             AND (user_id = ? OR module_id IN (?))",
            [$userId, $modules]
        );
    }
    
    private function userHas(int $userId, string $permission): bool
    {
        // Integration with FA's existing security system
        return check_user_access($userId, $permission);
    }
    
    private function getModuleAdminPermission(string $moduleId): string
    {
        $map = [
            'reports' => 'SA_REPORTS_ADMIN',
            'crm' => 'SA_CRM_ADMIN',
            'marketing' => 'SA_MARKETING_ADMIN',
            'todo' => 'SA_TODO_ADMIN'
        ];
        
        return $map[$moduleId] ?? 'SA_' . strtoupper($moduleId) . '_ADMIN';
    }
    
    private function getUserModuleAccess(int $userId): array
    {
        // Get list of modules user has access to
        // ... implementation
    }
}
```

### 5. Module Integration Example: Reports

```php
<?php
declare(strict_types=1);

namespace FA\Modules\Reports\Scheduler;

use FA\Scheduler\TaskProviderInterface;
use FA\Scheduler\SchedulableTaskInterface;

/**
 * Reports module task provider
 * Registers schedulable tasks for report automation
 */
class ReportsTaskProvider implements TaskProviderInterface
{
    public function getModuleId(): string
    {
        return 'reports';
    }
    
    public function getBasePermission(): string
    {
        return 'SA_REPORTS';
    }
    
    public function getTaskTypes(): array
    {
        return [
            'generate_report' => GenerateReportTask::class,
            'email_report' => EmailReportTask::class,
            'export_report' => ExportReportTask::class,
            'report_bundle' => ReportBundleTask::class
        ];
    }
    
    public function createTask(string $taskType, array $config): SchedulableTaskInterface
    {
        return match($taskType) {
            'generate_report' => new GenerateReportTask(
                $config['report_id'],
                $config['parameters'] ?? []
            ),
            'email_report' => new EmailReportTask(
                $config['report_id'],
                $config['recipients'],
                $config['parameters'] ?? [],
                $config['format'] ?? 'pdf'
            ),
            'export_report' => new ExportReportTask(
                $config['report_id'],
                $config['storage_type'],
                $config['storage_path'],
                $config['parameters'] ?? []
            ),
            'report_bundle' => new ReportBundleTask(
                $config['report_ids'],
                $config['recipients'],
                $config['bundle_name']
            ),
            default => throw new \InvalidArgumentException("Unknown task type: $taskType")
        };
    }
    
    public function getConfigurationUI(string $taskType): array
    {
        return match($taskType) {
            'generate_report' => [
                ['type' => 'select', 'name' => 'report_id', 'label' => 'Report', 'required' => true],
                ['type' => 'parameters', 'name' => 'parameters', 'label' => 'Parameters']
            ],
            'email_report' => [
                ['type' => 'select', 'name' => 'report_id', 'label' => 'Report', 'required' => true],
                ['type' => 'email_list', 'name' => 'recipients', 'label' => 'Recipients', 'required' => true],
                ['type' => 'select', 'name' => 'format', 'label' => 'Format', 'options' => ['pdf', 'excel', 'csv']],
                ['type' => 'parameters', 'name' => 'parameters', 'label' => 'Report Parameters']
            ],
            // ... other task types
        };
    }
    
    public function validateConfiguration(string $taskType, array $config): ValidationResult
    {
        $errors = [];
        
        // Common validation
        if (empty($config['report_id'])) {
            $errors[] = 'Report ID is required';
        }
        
        // Task-specific validation
        if ($taskType === 'email_report' && empty($config['recipients'])) {
            $errors[] = 'At least one recipient is required';
        }
        
        return new ValidationResult(empty($errors), $errors);
    }
}

/**
 * Example task: Generate and email report
 */
class EmailReportTask implements SchedulableTaskInterface
{
    public function __construct(
        private int $reportId,
        private array $recipients,
        private array $parameters,
        private string $format,
        private ?ReportServiceInterface $reportService = null,
        private ?EmailService $emailService = null
    ) {}
    
    public function execute(): TaskResult
    {
        try {
            // Generate report
            $report = $this->reportService->generate($this->reportId, $this->parameters);
            
            // Export to format
            $file = $report->export($this->format);
            
            // Send emails
            $sent = [];
            foreach ($this->recipients as $recipient) {
                $this->emailService->send(
                    to: $recipient,
                    subject: "Scheduled Report: {$report->getTitle()}",
                    body: "Please find attached your scheduled report.",
                    attachments: [$file]
                );
                $sent[] = $recipient;
            }
            
            return TaskResult::success(
                message: "Report emailed to " . count($sent) . " recipients",
                data: ['recipients' => $sent, 'file' => $file]
            );
            
        } catch (\Exception $e) {
            return TaskResult::failure($e->getMessage());
        }
    }
    
    public function canExecute(): bool
    {
        // Check if report service is available
        return $this->reportService !== null && $this->emailService !== null;
    }
    
    public function getMetadata(): TaskMetadata
    {
        return new TaskMetadata(
            id: "email_report_{$this->reportId}",
            name: "Email Report #{$this->reportId}",
            description: "Generate and email report to recipients",
            module: 'reports',
            scheduleType: TaskScheduleType::CRON
        );
    }
    
    public function getRequiredPermission(): string
    {
        // Require report-specific permission
        return "SA_REPORT_{$this->reportId}";
    }
    
    public function onSuccess(TaskResult $result): void
    {
        // Log successful execution
    }
    
    public function onFailure(\Exception $e): void
    {
        // Alert admin of failure
    }
}
```

## Database Schema

```sql
-- Scheduled tasks table
CREATE TABLE scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id VARCHAR(50) NOT NULL,
    task_type VARCHAR(100) NOT NULL,
    config JSON NOT NULL,
    metadata JSON NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'running', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    priority INT DEFAULT 5,
    enabled TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME,
    next_run DATETIME,
    last_run DATETIME,
    retry_count INT DEFAULT 0,
    max_retries INT DEFAULT 3,
    error_message TEXT,
    INDEX idx_status_nextrun (status, next_run),
    INDEX idx_module (module_id),
    INDEX idx_user (user_id),
    INDEX idx_priority (priority)
);

-- Task execution log
CREATE TABLE task_execution_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    status ENUM('success', 'failed', 'cancelled') NOT NULL,
    started_at DATETIME NOT NULL,
    completed_at DATETIME,
    duration INT,
    result JSON,
    error_message TEXT,
    FOREIGN KEY (schedule_id) REFERENCES scheduled_tasks(id) ON DELETE CASCADE,
    INDEX idx_schedule (schedule_id),
    INDEX idx_started (started_at)
);

-- Security permissions
INSERT INTO security_roles (id, role, description) VALUES
('SA_SCHEDULER', 'Schedule Tasks', 'Can schedule automated tasks'),
('SA_SCHEDULER_ADMIN', 'Scheduler Administrator', 'Full access to all scheduled tasks'),
('SA_SCHEDULER_VIEW', 'View Schedules', 'Can view scheduled tasks');
```

## UI Implementation

### Admin Panel: Global Scheduler View

Location: `admin/task_scheduler.php`

```php
// Shows all scheduled tasks across modules (filtered by access)
// Features:
// - Grid view of all tasks
// - Filter by module, status, user
// - Quick actions: pause, resume, cancel, edit
// - Execution history
// - Performance metrics
```

### Per-Module Scheduling UI

Each module provides its own scheduling interface:

**Reports Module**: `reporting/schedule_report.php`
```php
// Schedule a specific report
// - Report selection dropdown
// - Parameter configuration
// - Schedule settings (cron, recurring, one-time)
// - Recipient management
// - Preview next 5 run times
```

**CRM Module**: `modules/CRM/schedule_reminder.php`
```php
// Schedule CRM reminders/follow-ups
// - Contact/opportunity selection
// - Reminder type (meeting, follow-up, deadline)
// - Schedule settings
// - Notification channels
```

## Registration & Bootstrap

### Module Registration (in module's `init.php`)

```php
<?php
// modules/Reports/init.php

use FA\Scheduler\TaskProviderRegistry;
use FA\Modules\Reports\Scheduler\ReportsTaskProvider;

// Register task provider with scheduler
$registry = $container->get(TaskProviderRegistry::class);
$registry->register('reports', ReportsTaskProvider::class);
```

### Global Bootstrap

```php
<?php
// includes/scheduler_bootstrap.php

use FA\Scheduler\TaskScheduler;
use FA\Scheduler\TaskProviderRegistry;

// Initialize scheduler
$registry = new TaskProviderRegistry($container);
$scheduler = new TaskScheduler($db, $registry, $events, $accessControl, $logger);

// Register in container
$container->set(TaskScheduler::class, $scheduler);
$container->set(TaskProviderRegistry::class, $registry);
```

## Phase Implementation Plan

### Phase 1: Core Infrastructure (Week 1-2)
✅ Core interfaces (SchedulableTaskInterface, TaskProviderInterface)
✅ TaskScheduler implementation
✅ TaskProviderRegistry with DI
✅ Database schema
✅ Access control system
✅ Basic cron executor
✅ Reports module integration
✅ Admin UI for viewing/managing tasks

**Deliverable**: Can schedule report generation/email on cron schedule

### Phase 2: CRM Integration (Week 3)
✅ CRMTaskProvider implementation
✅ Meeting reminder tasks
✅ Follow-up tasks
✅ Inactive customer re-engagement
✅ CRM scheduling UI
✅ Notification channels (email + in-app)

**Deliverable**: CRM automated reminders and follow-ups

### Phase 3: Todo & Staff Management (Week 4)
✅ TodoTaskProvider
✅ Deadline reminder tasks
✅ Goal milestone alerts
✅ Overdue escalation
✅ Team notification tasks
✅ Todo scheduling UI

**Deliverable**: Automated todo/goal notifications

### Phase 4: Marketing Automation (Week 5-6)
✅ MarketingTaskProvider
✅ Social media post scheduling
✅ Content approval workflows
✅ Email campaign tasks
✅ A/B test scheduling
✅ Marketing dashboard UI
✅ Social media API integrations

**Deliverable**: Full marketing automation pipeline

### Phase 5: Advanced Workflows (Week 7-8)
✅ WorkflowTaskProvider
✅ Multi-step approval chains
✅ Conditional task execution
✅ Event-based triggers
✅ Workflow designer UI
✅ Integration webhooks

**Deliverable**: Complex workflow automation

## Security Model

### Permission Levels

```php
// Base scheduler access
'SA_SCHEDULER' => 'Can schedule tasks'

// Per-module scheduling (requires base + module access)
'SA_REPORTS' + 'SA_SCHEDULER' => 'Can schedule reports'
'SA_CRM' + 'SA_SCHEDULER' => 'Can schedule CRM tasks'
'SA_MARKETING' + 'SA_SCHEDULER' => 'Can schedule marketing tasks'

// Admin access (can manage all tasks across modules)
'SA_SCHEDULER_ADMIN' => 'Full scheduler administration'

// Per-module admin (can manage tasks for specific module)
'SA_REPORTS_ADMIN' => 'Can manage all report schedules'
'SA_CRM_ADMIN' => 'Can manage all CRM schedules'
```

### Access Rules

1. **User can schedule**: Has `SA_SCHEDULER` + module permission
2. **User can view**: Can see own tasks + tasks for modules they have access to
3. **User can edit**: Can edit own tasks
4. **User can cancel**: Can cancel own tasks
5. **Module admin**: Can manage all tasks for their module
6. **Scheduler admin**: Can manage all tasks across all modules

## Event System Integration

All scheduler operations dispatch events for hooks:

```php
// Lifecycle events
'scheduler.task.scheduled'
'scheduler.task.executing'
'scheduler.task.executed'
'scheduler.task.success'
'scheduler.task.failed'
'scheduler.task.cancelled'

// Module-specific events
'reports.task.scheduled'
'reports.task.executed'
'crm.reminder.sent'
'marketing.post.published'
```

## Extension Examples

### Future Module: HR

```php
<?php
namespace FA\Modules\HR\Scheduler;

class HRTaskProvider implements TaskProviderInterface
{
    public function getTaskTypes(): array
    {
        return [
            'birthday_greeting' => BirthdayGreetingTask::class,
            'performance_review_reminder' => PerformanceReviewTask::class,
            'timesheet_reminder' => TimesheetReminderTask::class,
            'onboarding_workflow' => OnboardingWorkflowTask::class
        ];
    }
}
```

### Future Module: Inventory

```php
<?php
namespace FA\Modules\Inventory\Scheduler;

class InventoryTaskProvider implements TaskProviderInterface
{
    public function getTaskTypes(): array
    {
        return [
            'low_stock_alert' => LowStockAlertTask::class,
            'reorder_point_check' => ReorderPointTask::class,
            'expiry_alert' => ExpiryAlertTask::class,
            'cycle_count_schedule' => CycleCountTask::class
        ];
    }
}
```

## Benefits of This Design

1. **Pluggable**: New modules just implement TaskProviderInterface
2. **DI-Based**: Loose coupling, easy testing
3. **SRP**: Each component has single responsibility
4. **Secure**: Granular access control tied to existing FA permissions
5. **Scalable**: Can handle thousands of scheduled tasks
6. **Event-Driven**: Integrates with existing event system
7. **UI Flexible**: Both global and per-module interfaces
8. **Extensible**: Easy to add new task types, triggers, channels

## Next Steps

Ready to implement Phase 1? This gives you:
- Complete core infrastructure
- Reports module fully integrated
- Working cron execution
- Admin UI

Then we progressively add CRM, Todo, Marketing, and Workflows.
