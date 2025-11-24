<?php
declare(strict_types=1);

namespace Ksfraser\PluginSystem\EventDispatcher;

use Ksfraser\PluginSystem\Interfaces\PluginEventDispatcherInterface;

/**
 * Null Event Dispatcher
 *
 * No-op implementation for testing or when events are not needed
 */
class NullEventDispatcher implements PluginEventDispatcherInterface
{
    /**
     * Dispatch an event (no-op)
     */
    public function dispatch(object $event): void
    {
        // No-op - events are ignored
    }

    /**
     * Add an event listener (no-op)
     */
    public function on(string $eventName, callable $listener): void
    {
        // No-op - listeners are ignored
    }
}