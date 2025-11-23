<?php
declare(strict_types=1);

namespace FA\Services;

use Psr\EventDispatcher\StoppableEventInterface;
use FA\Events\Event;

/**
 * Workflow Service
 *
 * Manages workflow definitions, execution, and integration with the event system.
 * Workflows are triggered by events and can perform complex business logic.
 */
class WorkflowService
{
    /**
     * Register a workflow to be triggered by an event
     *
     * @param string $eventName The event that triggers the workflow
     * @param callable $workflowHandler The workflow handler function
     */
    public static function registerWorkflow($eventName, callable $workflowHandler): void
    {
        EventManager::on($eventName, function(StoppableEventInterface $event) use ($workflowHandler) {
            try {
                $workflowHandler($event);
            } catch (\Exception $e) {
                // Log workflow execution error
                error_log("Workflow execution failed for event {$eventName}: " . $e->getMessage());
            }
        });
    }

    /**
     * Create a simple approval workflow
     *
     * @param string $eventName Event that triggers the workflow
     * @param array $approvers List of approver user IDs
     * @param callable $onApproved Callback when approved
     * @param callable $onRejected Callback when rejected
     */
    public static function createApprovalWorkflow(
        $eventName,
        array $approvers,
        callable $onApproved,
        callable $onRejected
    ): void {
        self::registerWorkflow($eventName, function(StoppableEventInterface $event) use ($approvers, $onApproved, $onRejected) {
            // Simple approval logic - in a real implementation, this would be more sophisticated
            // For now, just call the approved callback
            $onApproved($event);
        });
    }

    /**
     * Create a notification workflow
     *
     * @param string $eventName Event that triggers notifications
     * @param array $recipients List of notification recipients
     * @param string $message Notification message template
     */
    public static function createNotificationWorkflow(
        $eventName,
        array $recipients,
        $message
    ): void {
        self::registerWorkflow($eventName, function(StoppableEventInterface $event) use ($recipients, $message) {
            // Simple notification logic - in a real implementation, this would send actual notifications
            error_log("Notification workflow triggered: {$message} for event " . get_class($event));
        });
    }

    /**
     * Create a data transformation workflow
     *
     * @param string $eventName Event that triggers transformation
     * @param callable $transformer Data transformation function
     */
    public static function createTransformationWorkflow(
        $eventName,
        callable $transformer
    ): void {
        self::registerWorkflow($eventName, function(StoppableEventInterface $event) use ($transformer) {
            $transformer($event);
        });
    }

    /**
     * Create a conditional workflow that only executes based on conditions
     *
     * @param string $eventName Event that triggers the workflow
     * @param callable $condition Condition checker function
     * @param callable $action Action to perform if condition is met
     */
    public static function createConditionalWorkflow(
        $eventName,
        callable $condition,
        callable $action
    ): void {
        self::registerWorkflow($eventName, function(StoppableEventInterface $event) use ($condition, $action) {
            if ($condition($event)) {
                $action($event);
            }
        });
    }
}