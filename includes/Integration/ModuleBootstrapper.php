<?php
/**
 * Module Bootstrapper - Integrates all modules with Scheduler
 * This is the main entry point for the modular system
 */

declare(strict_types=1);

namespace FA\Integration;

use FA\Scheduler\TaskScheduler;
use FA\Scheduler\TaskProviderRegistry;
use FA\Scheduler\AccessControl;
use FA\Scheduler\Hook\HookManager as SchedulerHookManager;
use FA\CRM\Hook\HookManager as CRMHookManager;
use FA\CRM\Workflow\WorkflowEngine;
use FA\CRM\Task\CRMTaskProvider;
use FA\Database\DBALInterface;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class ModuleBootstrapper
{
    public function __construct(
        private ContainerInterface $container
    ) {}
    
    /**
     * Bootstrap all modules
     */
    public function bootstrap(): void
    {
        // Register CRM with scheduler
        $this->registerCRM();
        
        // Register Todo module
        $this->registerTodo();
        
        // Register Marketing module
        $this->registerMarketing();
        
        // Register Workflow module
        $this->registerWorkflow();
        
        // Register Reporting module
        $this->registerReporting();
        
        // Register Serial/Lot Tracking module
        $this->registerSerialTracking();
        
        // Setup cross-module workflows
        $this->setupCrossModuleWorkflows();
    }
    
    /**
     * Register CRM module
     */
    private function registerCRM(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('crm', CRMTaskProvider::class);
        
        // Initialize CRM workflow engine
        $crmHookManager = $this->container->get(CRMHookManager::class);
        $workflowEngine = $this->container->get(WorkflowEngine::class);
        
        // Workflow engine automatically registers its hooks in constructor
    }
    
    /**
     * Register Todo module
     */
    private function registerTodo(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('todo', \FA\Todo\Task\TodoTaskProvider::class);
        
        // Schedule recurring reminder checks
        $this->scheduleRecurringReminders();
    }
    
    /**
     * Register Marketing module
     */
    private function registerMarketing(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('marketing', \FA\Marketing\Task\MarketingTaskProvider::class);
        
        // Schedule campaign automation checks
        $this->scheduleMarketingAutomation();
    }
    
    /**
     * Register Workflow module
     */
    private function registerWorkflow(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('workflow', \FA\Workflow\Task\WorkflowTaskProvider::class);
        
        // Schedule workflow automation tasks
        $this->scheduleWorkflowAutomation();
    }
    
    /**
     * Register Reporting module
     */
    private function registerReporting(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('reporting', \FA\Reporting\Task\ReportingTaskProvider::class);
        
        // Schedule reporting tasks
        $this->scheduleReportingTasks();
    }
    
    /**
     * Register Serial/Lot Tracking module
     */
    private function registerSerialTracking(): void
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        $registry->register('serial_tracking', \FA\SerialTracking\Task\SerialTrackingTaskProvider::class);
        
        // Schedule serial tracking tasks
        $this->scheduleSerialTrackingTasks();
    }
    
    /**
     * Setup cross-module workflows
     */
    private function setupCrossModuleWorkflows(): void
    {
        // Example: When CRM opportunity is won, create celebration task in Todo
        $crmHooks = $this->container->get(CRMHookManager::class);
        
        $crmHooks->registerHook('Opportunities', 'after_save', function($bean) {
            if ($bean->isWon()) {
                $this->createCelebrationTask($bean);
            }
        }, 0);
        
        // Workflow integration: High-value opportunities require approval
        $crmHooks->registerHook('Opportunities', 'before_save', function($bean) {
            if ($bean->isNew() && $bean->amount >= 50000) {
                $this->startOpportunityApprovalWorkflow($bean);
            }
        }, 5);
        
        // Marketing integration: Campaigns require approval before sending
        // (handled in Marketing module's CampaignRepository)
    }
    
    /**
     * Schedule recurring reminder checks
     */
    private function scheduleRecurringReminders(): void
    {
        $scheduler = $this->container->get(TaskScheduler::class);
        
        // Daily reminder check at 8 AM
        $scheduler->schedule(
            userId: 1, // System user
            moduleId: 'todo',
            taskType: 'check_reminders',
            configuration: ['type' => 'all'],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 8 * * *',
            priority: \FA\Scheduler\Priority::NORMAL
        );
        
        // Goal progress check weekly on Monday
        $scheduler->schedule(
            userId: 1,
            moduleId: 'todo',
            taskType: 'check_goal_progress',
            configuration: ['type' => 'active'],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 9 * * 1',
            priority: \FA\Scheduler\Priority::NORMAL
        );
    }
    
    /**
     * Schedule marketing automation tasks
     */
    private function scheduleMarketingAutomation(): void
    {
        $scheduler = $this->container->get(TaskScheduler::class);
        
        // Check for campaigns to send every hour
        $scheduler->schedule(
            userId: 1,
            moduleId: 'marketing',
            taskType: 'send_scheduled_campaigns',
            configuration: [],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 * * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
    }
    
    /**
     * Schedule workflow automation tasks
     */
    private function scheduleWorkflowAutomation(): void
    {
        $scheduler = $this->container->get(TaskScheduler::class);
        
        // Check for workflow timeouts every hour
        $scheduler->schedule(
            userId: 1,
            moduleId: 'workflow',
            taskType: 'check_timeouts',
            configuration: [],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '15 * * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
        
        // Send approval reminders daily at 9 AM
        $scheduler->schedule(
            userId: 1,
            moduleId: 'workflow',
            taskType: 'send_reminders',
            configuration: ['threshold' => 3600], // 1 hour
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 9 * * *',
            priority: \FA\Scheduler\Priority::NORMAL
        );
        
        // Cleanup completed workflows monthly
        $scheduler->schedule(
            userId: 1,
            moduleId: 'workflow',
            taskType: 'cleanup_completed',
            configuration: ['days' => 30],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 2 1 * *',
            priority: \FA\Scheduler\Priority::LOW
        );
    }
    
    /**
     * Schedule reporting automation tasks
     */
    private function scheduleReportingTasks(): void
    {
        $scheduler = $this->container->get(TaskScheduler::class);
        
        // Generate scheduled reports hourly
        $scheduler->schedule(
            userId: 1,
            moduleId: 'reporting',
            taskType: 'generate_scheduled_reports',
            configuration: [],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 * * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
        
        // Cleanup execution log monthly
        $scheduler->schedule(
            userId: 1,
            moduleId: 'reporting',
            taskType: 'cleanup_execution_log',
            configuration: ['days' => 90],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 3 1 * *',
            priority: \FA\Scheduler\Priority::LOW
        );
        
        // Send weekly report digest
        $scheduler->schedule(
            userId: 1,
            moduleId: 'reporting',
            taskType: 'send_report_digest',
            configuration: ['period' => 'weekly'],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 9 * * 1',
            priority: \FA\Scheduler\Priority::NORMAL
        );
    }
    
    /**
     * Schedule serial/lot tracking automation tasks
     */
    private function scheduleSerialTrackingTasks(): void
    {
        $scheduler = $this->container->get(TaskScheduler::class);
        
        // Check expiring lots daily at 8 AM
        $scheduler->schedule(
            userId: 1,
            moduleId: 'serial_tracking',
            taskType: 'check_expiring_lots',
            configuration: ['days_threshold' => 30],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 8 * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
        
        // Alert expired lots daily at 9 AM
        $scheduler->schedule(
            userId: 1,
            moduleId: 'serial_tracking',
            taskType: 'alert_expired_lots',
            configuration: [],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 9 * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
        
        // Process active recalls every 4 hours
        $scheduler->schedule(
            userId: 1,
            moduleId: 'serial_tracking',
            taskType: 'process_recalls',
            configuration: [],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 */4 * * *',
            priority: \FA\Scheduler\Priority::HIGH
        );
        
        // Cleanup old traceability records monthly
        $scheduler->schedule(
            userId: 1,
            moduleId: 'serial_tracking',
            taskType: 'cleanup_old_traces',
            configuration: ['days_to_keep' => 730],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 3 2 * *',
            priority: \FA\Scheduler\Priority::LOW
        );
        
        // Warranty expiration alerts weekly on Monday
        $scheduler->schedule(
            userId: 1,
            moduleId: 'serial_tracking',
            taskType: 'warranty_expiration_alerts',
            configuration: ['days_threshold' => 60],
            scheduleType: \FA\Scheduler\TaskScheduleType::CRON,
            cronExpression: '0 10 * * 1',
            priority: \FA\Scheduler\Priority::NORMAL
        );
    }
    
    /**
     * Create celebration task when opportunity is won
     */
    private function createCelebrationTask($opportunity): void
    {
        $taskRepo = $this->container->get(\FA\Todo\Repository\TaskRepository::class);
        
        $task = new \FA\Todo\Entity\Task();
        $task->setTitle("🎉 Celebrate win: {$opportunity->name}");
        $task->setDescription("Opportunity worth \${$opportunity->amount} was won!");
        $task->setPriority(\FA\Todo\Entity\TaskPriority::NORMAL);
        $task->setCategory('celebration');
        $task->addTag('crm');
        $task->addTag('win');
        
        $taskRepo->save($task);
    }
    
    /**
     * Start approval workflow for high-value opportunity
     */
    private function startOpportunityApprovalWorkflow($opportunity): void
    {
        $workflowRepo = $this->container->get(\FA\Workflow\Repository\WorkflowRepository::class);
        $workflowEngine = $this->container->get(\FA\Workflow\Engine\WorkflowEngine::class);
        
        // Create workflow from template
        $templateService = $this->container->get(\FA\Workflow\Service\WorkflowTemplateService::class);
        $workflow = $templateService->createFromTemplate('po_multi_approval', [
            'id' => 'opportunity_approval',
            'name' => 'High-Value Opportunity Approval'
        ]);
        
        // Register workflow
        $workflowEngine->registerWorkflow($workflow);
        
        // Create instance
        $state = new \FA\Workflow\State\WorkflowState('draft', [
            'opportunity_id' => $opportunity->id,
            'amount' => $opportunity->amount,
            'name' => $opportunity->name
        ]);
        
        $instanceId = $workflowRepo->createInstance(
            'opportunity_approval',
            'Opportunities',
            $opportunity->id,
            $state,
            $opportunity->assigned_user_id ?? 1
        );
        
        // Set opportunity to pending approval status
        $opportunity->status = 'pending_approval';
    }
    
    /**
     * Get system health status
     */
    public function getHealthStatus(): array
    {
        $registry = $this->container->get(TaskProviderRegistry::class);
        
        return [
            'scheduler' => 'active',
            'modules' => [
                'crm' => $registry->getProvider('crm') ? 'registered' : 'not_registered',
                'todo' => $registry->getProvider('todo') ? 'registered' : 'not_registered',
                'marketing' => $registry->getProvider('marketing') ? 'registered' : 'not_registered',
                'workflow' => $registry->getProvider('workflow') ? 'registered' : 'not_registered',
                'reporting' => $registry->getProvider('reporting') ? 'registered' : 'not_registered',
                'serial_tracking' => $registry->getProvider('serial_tracking') ? 'registered' : 'not_registered'
            ],
            'task_types' => $registry->getAllTaskTypes()
        ];
    }
}
